<?php

//funcao para verificacao de request
function itisreally($valor) {
	if (isset($valor)) {

		if ($valor === 0 || $valor === "0") {
			return (int) $valor;
		}

		if (!empty($valor)) {
			return $valor;
		}
	} 

	return false;
}

//medir dias de diferença entre datas
function diff_days($datai, $datao) {
	$data_inicio = new DateTime(date("Y-m-d", strtotime($datai)));
	$data_fim = new DateTime(date("Y-m-d", strtotime($datao)));

	// Resgata diferença entre as datas
	$dateInterval = $data_inicio->diff($data_fim);
	$anos_em_d = $dateInterval->y * 365;
	$meses_em_d = $dateInterval->m * 30;
	$dias = $dateInterval->d;

	return $anos_em_d + $meses_em_d + $dias;
}

// medir diferença de horas para a hora atual
function diff_horas($datai) {
	$data_inicio = new DateTime(date("Y-m-d H:i:s", strtotime($datai)));
	$data_fim = new DateTime(date("Y-m-d H:i:s"));

	// Resgata diferença entre as datas
	$dateInterval = $data_inicio->diff($data_fim);
	$anos_em_h = $dateInterval->y * 8760;
	$meses_em_h = $dateInterval->m * 730;
	$dias_em_h = $dateInterval->d * 24;
	$horas = $dateInterval->h;

	return $anos_em_h + $meses_em_h + $dias_em_h + $horas;
}

//cryptografa img base64
function cryptImg($urlimg) {
	$ext = mime_content_type($urlimg);
	return "data:$ext;base64,".base64_encode(file_get_contents($urlimg));
}

// mostra a semana do ano
function semana_do_ano($dia,$mes,$ano){
	return intval(date('z', mktime(0,0,0,$mes,$dia,$ano)) / 7) + 1;
}

// cria GUID
function guidv4($data = null) {
	$data = $data ?? random_bytes(16);
	assert(strlen($data) == 16);
	$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
	$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
	return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// create slug
function slug(string $texto) {

	# lowercase, tirar acentos, substituir espaços por menos
	$slug = strtolower(preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $texto)));

	return $slug;

}

?>