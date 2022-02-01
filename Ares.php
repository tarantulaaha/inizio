<?php


class Ares {
    function curl_get_content($url, $post = "", $refer = "", $usecookie = false)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);

        if ($post) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        }

        if ($refer) {
            curl_setopt($curl, CURLOPT_REFERER, $refer);
        }

        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/6.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.7) Gecko/20050414 Firefox/1.0.3");
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($curl, CURLOPT_TIMEOUT_MS, 5000);

        if ($usecookie) {
            curl_setopt($curl, CURLOPT_COOKIEJAR, $usecookie);
            curl_setopt($curl, CURLOPT_COOKIEFILE, $usecookie);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $html = curl_exec($curl);
        if (curl_error($curl)) {
            echo 'Loi CURL : ' . (curl_error($curl));
        }
        curl_close($curl);
        return $html;
    }
    private $_url = 'http://wwwinfo.mfcr.cz/cgi-bin/ares/darv_bas.cgi';
    private $_response;

    public function search($ico) {
        if (!is_numeric($ico) or strlen($ico) < 7 or strlen($ico) > 8) {
            throw new Exception('IČ musí být číslo o délce 7-8 znaků', 1);
        }else{
            $this->_url.="?ico={$ico}";
        }
        $this->_response= $this->curl_get_content($this->_url);
        return $this->parseData();
    }

    public function parseData() {
        $xml = new SimpleXMLElement($this->_response);
        $ns = $xml->getDocNamespaces();
        $res = $xml->children($ns['are'])->children($ns['D']);
        if (isset($res->E)) {
            throw new Exception('Error: ' . $res->E->ET, 404);
        }
        $sub = $res->VBAS;
        $sub_addr = $sub->AA;
        date_default_timezone_set("Europe/Prague");
        $data['date']=time();
        $data['company'] = trim(strval($sub->OF));
        $data['ic'] = trim(strval($sub->ICO));
        $data['ic_duplicate'] = $data['ic'];
        $data['dic'] = trim(strval($sub->DIC));
        $data['city'] = trim(strval($sub_addr->N));
        $data['country'] = trim(strval($sub_addr->NS));
        $data['zip'] = trim(strval($sub_addr->PSC));
        $data['street'] = trim(strval($sub_addr->NU)) . ' ' . trim(strval($sub_addr->CD));
        if (isset($sub_addr->CO))
            $data['street'] .= '/' . trim(strval($sub_addr->CO));

        /**
         * @link http://wwwinfo.mfcr.cz/ares/aresPrFor.html.cz
         */
        $cisloPravniFormy = $sub->PF->KPF;
        if($cisloPravniFormy <= 110) {
            $data['record'] = 'Záznam podnikatele v živnostenském rejstříku vede: ' . $sub->RRZ->ZU->NZU;
        }
        else {
            $data['record'] = 'Záznam společnosti vede: ' . $sub->ROR->SZ->SD->T . ', spisová značka ' . $sub->ROR->SZ->OV;
        }

        return $data;
    }

}