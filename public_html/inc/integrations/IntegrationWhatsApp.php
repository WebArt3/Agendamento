<?php

use Netflie\WhatsAppCloudApi\WhatsAppCloudApi;
use Netflie\WhatsAppCloudApi\WebHook;

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

                switch ($notification->mimeType()) {
                    
                    case "audio/ogg":
                        $tipo = "audio";
                        break;
                    
                    case "image/jpeg":
                        $tipo = "imagem";
                        break;
                    
                    case "video/mp4":
                        $tipo = "video";
                        break;

                    case "application/pdf":
                        $tipo = "pdf";
                        break;

                }

                $this->sendText($notification->customer()->phoneNumber(), "Recebi sua media! é $tipo");

            }

            elseif (method_exists($notification, "latitude")) {

                // latitude, longitude, name, address

                $this->sendText($notification->customer()->phoneNumber(), "Recebi sua localização!");

            }

        }

        return $this->view->sucesso();

    }

    public function webhook_debug($var) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://fbaa-177-234-190-233.ngrok-free.app");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $txt_not);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: text/plain'));
        $server_output = curl_exec($ch);
        curl_close($ch);

    }

    public function webhook_resposta(string $telefone, string $nome, string $mensagem=null) {

        $this->sendText($telefone, "Olá $nome, recebi sua mensagem!");

        return;

    }

}