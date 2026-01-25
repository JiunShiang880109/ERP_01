<?php

namespace App\Traits;
use DB;
trait Reject_InvalidTraits
{   //註銷
    public function Invalid_MIG(){

    }
    //B2B
    public function Invalid_XMLA0501(){

    }
    //B2C
    public function Invalid_XMLC0701(){
        $filed = '<?xml version="1.0" encoding="utf-8"?>
        <CancelInvoice xsi:schemaLocation="urn:GEINV:eInvoiceMessage:C0701:3.2 C0701.xsd" xmlns="urn:GEINV:eInvoiceMessage:C0701:3.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
          <CancelInvoiceNumber>' .$value['invoiceNumber'];
        $filed = $filed.' </CancelInvoiceNumber>
          <InvoiceDate>' . $value['invoiceDate'];
        $filed = $filed.'</InvoiceDate>

          <BuyerId>' .$value['buyerTax'];
        $filed = $filed.'</BuyerId>

          <SellerId>' . $value['sellerTax'];
        $filed = $filed.'</SellerId>

          <CancelDate>' .$value['cancelDate'];
        $filed = $filed.'</CancelDate>

          <CancelTime>' .$value['cancelTime'];
        $filed = $filed.'</CancelTime>


          <CancelReason>' .$value['cancelReason'];
        $filed =$filed.'</CancelReason>
        </CancelInvoice>';

        //return $filed;
        $xml = new \SimpleXMLElement($filed);
        $current = date("Ymd");

        $file_path = '../../dalimarket/image/mig/' . $current;
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file_path = '../../dalimarket/image/mig/' . $current . '/C0701';
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file_path = '../../dalimarket/image/invoice/' . $value['rejectNum'];
        if (!file_exists($file_path)) {

            mkdir($file_path, 0777, true);
        }
        $file = '../../dalimarket/image/invoice/' . $value['rejectNum'] . '/' . $value['rejectNum'] . '_C0701.xml';

        $xml->asXml($file);
        $files = '../../dalimarket/image/mig/' . $current . '/C0701/' . $value['invoiceNumber'] . '.xml';

        $xml->asXml($files);
        return $filed;
    }

}
