<?php

use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Netflie\WhatsAppCloudApi\WebHook;
use Netflie\WhatsAppCloudApi\Message\OptionsList\Row;
use Netflie\WhatsAppCloudApi\Message\OptionsList\Section;
use Netflie\WhatsAppCloudApi\Message\OptionsList\Action;
use Netflie\WhatsAppCloudApi\Message\Media\LinkID;
use Netflie\WhatsAppCloudApi\Message\Media\MediaObjectID;

class IntegrationWhatsApp extends IntegrationRoot {

    private $whatsapp;

    // construct
    public function __construct() {

        parent::__construct();

        $this->whatsapp = new WhatsAppCloudApi([
            'from_phone_number_id' => WHATSAPP_ID,
            'access_token' => WHATSAPP_KEY,
        ]);

    }

    // envia mensagem de texto
    public function sendText(string $numero, string $text) {
        
        $this->whatsapp->sendTextMessage($numero, $text);

    }

    // envia mensagem de lista
    public function sendList(string $numero, string $title, string $description, string $footer, string $button, array $sections) {

        $secoes = [];

        foreach ($sections as $section) {

            $rows = [];

            foreach ($section["rows"] as $key => $row) {
                $rows[] = new Row($row["id"], $row["title"], $row["description"]);
            }

            $secoes[] = new Section($section["title"], $rows);

        }

        $action = new Action($button, $secoes);

        $this->whatsapp->sendList($numero,
            $title,
            $description,
            $footer,
            $action
        );

    }

    // envia mensagem de media
    public function sendMedia(string $numero, string $media, string $caption=null, string $filename=null) {

        $response = $this->whatsapp->uploadMedia($media);

        if (array_key_exists('id', $response->decodedBody())) {
            $media_id = new MediaObjectID($response->decodedBody()['id']);
        } else {
            unlink($media);
            $this->view->erro("Erro ao enviar media", "media_not_sent", 500, true);
        }

        // pega extensao de media
        $frags = explode(".", $media);
        $ext = end($frags);

        // envio por tipo
        switch ($ext) {
            case 'jpg':
            case 'png':
            case 'jpeg':
                $this->whatsapp->sendImage($numero, $media_id, $caption);
                break;

            case 'mp4':
                $this->whatsapp->sendVideo($numero, $media_id, $caption ? $caption : "Video");
                break;

            case 'ogg':
            case 'mp3':
                $this->whatsapp->sendAudio($numero, $media_id, $caption);
                break;

            case 'pdf':
            case 'docx':
            case 'xlsx':
            case 'pptx':
            case 'doc':
            case 'xls':
            case 'ppt':
            case 'txt':
            case 'csv':
                $this->whatsapp->sendDocument($numero, $media_id, $filename, $caption);
                break;

            default:
                $this->view->erro("Extensão de arquivo não suportada", "extention_not_supported", 500, true);
                break;
        }

    }

    // recebe mensagens no webhook
    public function webhook() {

        $webhook = new WebHook();

        $debug = false;


        if (isset($this->app->get['hub_verify_token'])) {

            echo $webhook->verify($this->app->get, WHATSAPP_WEBHOOK_KEY);
            die;
            
        }

        $payload = file_get_contents('php://input');
        $notification = $webhook->read(json_decode($payload, true));

        if ($payload && $notification) {

            // DEBUG WEBHOOK SYSTEM
            if ($debug) {
    
                $txt_not = print_r($notification, true);

                $this->webhook_debug($txt_not);

                die;

            }            

            if (method_exists($notification, "message")) {

                $this->webhook_resposta($notification->customer()->phoneNumber(), $notification->customer()->name(), $notification->message());

            }

            elseif (method_exists($notification, "itemId")) {

                $this->webhook_resposta($notification->customer()->phoneNumber(), $notification->customer()->name(), $notification->itemId());

            }

            elseif (method_exists($notification, "imageId")) {

                $tipo = "imagem";

                $accept = [
                    ["mimetype" => "audio/ogg", "tipo" => "audio", "ext" => "ogg"],
                    ["mimetype" => "audio/mp3", "tipo" => "audio", "ext" => "mp3"],
                    ["mimetype" => "image/jpeg", "tipo" => "imagem", "ext" => "jpg"],
                    ["mimetype" => "image/png", "tipo" => "imagem", "ext" => "png"],
                    ["mimetype" => "video/mp4", "tipo" => "video", "ext" => "mp4"],
                    ["mimetype" => "application/pdf", "tipo" => "pdf", "ext" => "pdf"],
                    ["mimetype" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "tipo" => "word", "ext" => "docx"],
                    ["mimetype" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "tipo" => "excel", "ext" => "xlsx"],
                    ["mimetype" => "application/vnd.openxmlformats-officedocument.presentationml.presentation", "tipo" => "powerpoint", "ext" => "pptx"],
                ];

                // verifica se mimetype é aceito
                $mimetype = $notification->mimeType();
                if (in_array(explode(";", $mimetype)[0], array_column($accept, 'mimetype'))) {
                    $type = $accept[array_search($mimetype, array_column($accept, 'mimetype'))];
                } else {
                    $this->sendText($notification->customer()->phoneNumber(), "[Sistema] Desculpe, não recebemos esse formato de arquivo, tente enviar arquivos com as seguintes extensões: pdf, docx, xlsx, pptx, jpg, png, mp4, ogg");
                    return;
                }

                // download media
                $media = $this->whatsapp->downloadMedia($notification->imageId());

                // salva media
                $filename = md5($notification->imageId()) . "." . $type['ext'];
                $filepath = PATH_UPLOADS . $filename;

                file_put_contents($filepath, $media->body());

                // verifica se tem legenda
                $caption = $notification->caption() ?? null;

                $this->webhook_resposta($notification->customer()->phoneNumber(), $notification->customer()->name(), $caption, $filename);

            }

            elseif (method_exists($notification, "latitude")) {

                // latitude, longitude, name, address

                $this->sendText($notification->customer()->phoneNumber(), "Recebi sua localização!");

            }

        }

        return $this->view->sucesso();

    }

    public function webhook_debug($var, $content_type = 'text/plain') {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://e3d5-177-234-190-41.ngrok-free.app");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $var);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array("Content-Type: $content_type"));
        $server_output = curl_exec($ch);
        curl_close($ch);

    }

    public function websocket_send($url, $values, $method="POST") {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://ee0183cc5f84.herokuapp.com$url");

        if ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($values));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER,     array("Content-Type: application/json"));
        }

        $server_output = curl_exec($ch);
        curl_close($ch);

        return $server_output;

    }

    public function webhook_resposta(string $telefone, string $nome, string $mensagem=null, string $media=null) {

        $this->sendText($telefone, "Olá $nome, recebi sua mensagem!");

        return;

    }

}