<?php

namespace JansenFelipe\CnpjGratis;

use Exception;
use JansenFelipe\Utils\Utils as Utils;
use Symfony\Component\DomCrawler\Crawler;

class CnpjGratis {

    /**
     * Metodo para capturar o captcha e cookie para enviar no metodo de consulta
     *
     * @throws Exception
     * @return array Retorna Cookie e CaptchaBase64
     */
    public static function getParams()
    {
        $data = self::request('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao3.asp');
        $cookie = $data['headers']['Set-Cookie'];
        $date = new \DateTime();
        $image = self::request('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/captcha/gerarCaptcha.asp', [], [
            "Pragma: no-cache",
            "Origin: http://www.receita.fazenda.gov.br",
            "Host: www.receita.fazenda.gov.br",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Accept-Encoding: gzip, deflate",
            "Referer: http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/cnpjreva_solicitacao3.asp",
            "Cookie: flag=1; $cookie",
            "Connection: keep-alive"
        ]);
        if(@imagecreatefromstring($image['response'])==false){
            return array(
                'cookie' => $cookie,
                'captchaBase64' => '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'
            );
        }
        
        return array(
            'cookie' => $cookie,
            'captchaBase64' => base64_encode($image['response'])
        );
    }

    /**
     * Metodo para realizar a consulta
     *
     * @param  string $cnpj CNPJ
     * @param  string $captchaSolved CAPTCHA
     * @param  string $cookie COOKIE
     * @throws Exception
     * @return array  Dados da empresa
     */
    public static function consulta($cnpj, $captchaSolved, $cookie)
    {
        $result = array();

        if (!Utils::isCnpj($cnpj))
            throw new Exception('O CNPJ informado não é válido');

         $headers = [
            "Host: www.receita.fazenda.gov.br",
            "Origin: http://www.receita.fazenda.gov.br",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
            "Accept-Language: pt-BR,pt;q=0.9,en-US;q=0.8,en;q=0.7",
            "Accept-Encoding: gzip, deflate",
            "Content-Type: application/x-www-form-urlencoded",
            "Referer: http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_solicitacao3.asp",
            "Cookie: flag=1; $cookie",
            "Upgrade-Insecure-Requests: 1",
            "Connection: keep-alive"
        ];
        
        $params = [
            'origem' => 'comprovante',
            'cnpj' => $cnpj,
            'txtTexto_captcha_serpro_gov_br' => $captchaSolved,
            'submit1' => 'Consultar',
            'search_type' => 'cnpj'
        ];

        $data = self::request('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/valida.asp', $params, $headers);
        
        //Remove as novas linhas
        $text = preg_replace('/\s+/', ' ', trim($data['response']));

        //muda o encoding de binary para utf-8
        $html = iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);

        //instancia um novo objeto crawler
        $crawler = new Crawler($html);

        if (strpos($crawler->html(), '<b>Erro na Consulta</b>') !== false)
        {
            throw new Exception('Erro ao consultar. Confira se você digitou corretamente os caracteres fornecidos na imagem.', 98);
        }

        if ($crawler->filter('body > table:nth-child(3) font:nth-child(1)')->count() > 0)
            throw new Exception('Erro ao consultar. O CNPJ informado não existe no cadastro.', 99);

        $td = $crawler->filterXPath('//td');;

        foreach ($td->filter('td') as $td) {
            $td = new Crawler($td);

            if ($td->filter('font:nth-child(1)')->count() > 0) {
                $key = trim(strip_tags(preg_replace('/\s+/', ' ', $td->filter('font:nth-child(1)')->html())));

                switch ($key) {
                    case 'NOME EMPRESARIAL': $key = 'razao_social';
                        break;
                    case 'TÍTULO DO ESTABELECIMENTO (NOME DE FANTASIA)': $key = 'nome_fantasia';
                        break;
                    case 'CÓDIGO E DESCRIÇÃO DA ATIVIDADE ECONÔMICA PRINCIPAL': $key = 'cnae_principal';
                        break;
                    case 'CÓDIGO E DESCRIÇÃO DAS ATIVIDADES ECONÔMICAS SECUNDÁRIAS': $key = 'cnaes_secundario';
                        break;
                    case 'CÓDIGO E DESCRIÇÃO DA NATUREZA JURÍDICA' : $key = 'natureza_juridica';
                        break;
                    case 'LOGRADOURO': $key = 'logradouro';
                        break;
                    case 'NÚMERO': $key = 'numero';
                        break;
                    case 'COMPLEMENTO': $key = 'complemento';
                        break;
                    case 'CEP': $key = 'cep';
                        break;
                    case 'BAIRRO/DISTRITO': $key = 'bairro';
                        break;
                    case 'MUNICÍPIO': $key = 'cidade';
                        break;
                    case 'UF': $key = 'uf';
                        break;
                    case 'SITUAÇÃO CADASTRAL': $key = 'situacao_cadastral';
                        break;
                    case 'DATA DA SITUAÇÃO CADASTRAL': $key = 'situacao_cadastral_data';
                        break;
                    case 'MOTIVO DE SITUAÇÃO CADASTRAL': $key = 'motivo_situacao_cadastral';
                        break;
                    case 'SITUAÇÃO ESPECIAL': $key = 'situacao_especial';
                        break;
                    case 'DATA DA SITUAÇÃO ESPECIAL': $key = 'situacao_especial_data';
                        break;
                    case 'TELEFONE': $key = 'telefone';
                        break;
                    case 'ENDEREÇO ELETRÔNICO': $key = 'email';
                        break;
                    case 'ENTE FEDERATIVO RESPONSÁVEL (EFR)': $key = 'ente_federativo_responsavel';
                        break;
                    case 'DATA DE ABERTURA': $key = 'data_abertura';
                        break;
                    default: $key = null;
                        break;
                }


                if (!is_null($key)) {
                    $bs = $td->filter('font > b');
                    foreach ($bs as $b) {
                        $b = new Crawler($b);

                        $str = trim(preg_replace('/\s+/', ' ', $b->html()));
                        $attach = htmlspecialchars_decode($str);

                        if ($bs->count() == 1)
                            $result[$key] = $attach;
                        else
                            $result[$key][] = $attach;
                    }
                }
            }
        }

        if(isset($result['telefone']) && $result['telefone'] != '') {
            $posBarra = strpos($result['telefone'], '/');
            if ($posBarra > 0) {
                $result['telefone2'] = substr($result['telefone'], $posBarra + 1, strlen($result['telefone']) - $posBarra);
                $result['telefone'] = substr($result['telefone'], 0, $posBarra - 1);
            }
        }

        return $result;
    }

    /**
     * Send request
     *
     * @param $uri
     * @param array $data
     * @param array $headers
     *
     * @return array
     */
    private static function request($uri, array $data = [], array $headers = [])
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL            => $uri,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HEADER => 1,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT_MS     => 30000
        ]);

        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }

        $response = curl_exec($curl);

        $size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);

        curl_close($curl);

        $headers = [];

        foreach (explode(PHP_EOL, substr($response, 0, $size)) as $i)
        {
            $t = explode(':', $i, 2);
            if(isset($t[1]))
                $headers[trim($t[0])] = trim($t[1]);
        }

        $response = substr($response, $size);

        return compact('response', 'headers');
    }
}
