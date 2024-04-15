<?php

namespace App\Http\Controllers\Vouchers;

use App\Http\Resources\Vouchers\VoucherResource;
use App\Services\VoucherService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Jobs\VoucherRegisterJob;

use Illuminate\Support\Facades\Storage;

class StoreVouchersHandler
{
    public function __construct(private readonly VoucherService $voucherService)
    {
    }

    public function __invoke(Request $request): Response
    {
        $file1 = '<?xml version="1.0" encoding="UTF-8"?>
        <Invoice xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
          <!-- GENERADO DESDE WWW.NUBEFACT.COM -->
          <ext:UBLExtensions>
            <ext:UBLExtension>
              <ext:ExtensionContent>
                <ds:Signature Id="NubeFacTSign">
                  <ds:SignedInfo>
                    <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
                    <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
                    <ds:Reference URI="">
                      <ds:Transforms>
                        <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
                      </ds:Transforms>
                      <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
                      <ds:DigestValue>HoZ9qt4fOJ/YuEVl1AXGYzWMBcnbmYIKElJNDFLH4O4=</ds:DigestValue>
                    </ds:Reference>
                  </ds:SignedInfo>
                  <ds:SignatureValue>v2IQW0X/WCqZsgFGsQEdcrGOxg9NcWa3tJwdIQar6FcUYd1VN/O5T+pnDzlfOqQ+GldTfO76ldDUPO1AJNRH8Cj4+rA7UoxzvSJYx/lHSi423S3LRq6pI+EVVDL45MAMziW7iN+7KZQdt9Rc6dTgqbUEihKaR47hBVEAr+C2m4T3omcln8rzyOcmttwMO2mX1zH0w6IWHrTtcCvr94VVkAaPSooq21QTYlDWs/NHp5dOTjbk5C1CHH1EH/dsdMarEXXlUi1TUCCKjfzV+ouiVfg+5VHUMjDg1etmcWUttilbc7NtO0jloWM/M5UrKOKLqg5huBrSEWgTBhfxdZFMKg==</ds:SignatureValue>
                  <ds:KeyInfo>
                    <ds:X509Data>
                      <ds:X509SubjectName>DC=LLAMA.PE SA,C=PE,ST=LIMA,L=LIMA,O=TU EMPRESA S.A.,OU=DNI 9999999 RUC 20600695771 - CERTIFICADO PARA DEMOSTRACI\xC3\x93N,CN=NOMBRE REPRESENTANTE LEGAL - CERTIFICADO PARA DEMOSTRACI\xC3\x93N,emailAddress=demo@llama.pe</ds:X509SubjectName>
                      <ds:X509Certificate>MIIE9zCCA9+gAwIBAgIIcTdHJpbkFzcwDQYJKoZIhvcNAQEFBQAwggENMRswGQYKCZImiZPyLGQBGRYLTExBTUEuUEUgU0ExCzAJBgNVBAYTAlBFMQ0wCwYDVQQIDARMSU1BMQ0wCwYDVQQHDARMSU1BMRgwFgYDVQQKDA9UVSBFTVBSRVNBIFMuQS4xRTBDBgNVBAsMPEROSSA5OTk5OTk5IFJVQyAyMDYwMDY5NTc3MSAtIENFUlRJRklDQURPIFBBUkEgREVNT1NUUkFDScOTTjFEMEIGA1UEAww7Tk9NQlJFIFJFUFJFU0VOVEFOVEUgTEVHQUwgLSBDRVJUSUZJQ0FETyBQQVJBIERFTU9TVFJBQ0nDk04xHDAaBgkqhkiG9w0BCQEWDWRlbW9AbGxhbWEucGUwHhcNMjAwODMxMTY0MzA4WhcNMjIwODMxMTY0MzA4WjCCAQ0xGzAZBgoJkiaJk/IsZAEZFgtMTEFNQS5QRSBTQTELMAkGA1UEBhMCUEUxDTALBgNVBAgMBExJTUExDTALBgNVBAcMBExJTUExGDAWBgNVBAoMD1RVIEVNUFJFU0EgUy5BLjFFMEMGA1UECww8RE5JIDk5OTk5OTkgUlVDIDIwNjAwNjk1NzcxIC0gQ0VSVElGSUNBRE8gUEFSQSBERU1PU1RSQUNJw5NOMUQwQgYDVQQDDDtOT01CUkUgUkVQUkVTRU5UQU5URSBMRUdBTCAtIENFUlRJRklDQURPIFBBUkEgREVNT1NUUkFDScOTTjEcMBoGCSqGSIb3DQEJARYNZGVtb0BsbGFtYS5wZTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAPdMhCKhkVckQBweJf9cxKCVNIi5ggC2F5qcPgET2ejSPA2EjCE4gms4UqhRF9G5fumkNotHlgXxFoqrYRxIz8y6V1cTh6+D3/487lslSIgz6J4712AKBmL9d24lfMLD8CvZ9WPT4M4A67KLUZrfgZh8/SrdJzpXfOdhNfXTopMlsv2Cy3wxvZQ+gKheTRUOjFa99YGkjoJ8fzDiHzh+j72hTDZ2kTyO6Cz8vPcFYSVAXWlLYj4ZW23tml5+rT9ZnBGvpPPC8cGveVVdcHQt/4mvavmrLbNw6yZwYv2pJ6vOq0m28/ur+6bneVj0jmb2YaBnh3qcbfOBaqzIWPpvHg8CAwEAAaNXMFUwHQYDVR0OBBYEFAjmmpTDRPV+Ws2x4z4ujUKJXf56MB8GA1UdIwQYMBaAFAjmmpTDRPV+Ws2x4z4ujUKJXf56MBMGA1UdJQQMMAoGCCsGAQUFBwMBMA0GCSqGSIb3DQEBBQUAA4IBAQDr/i5a5IN1U8DKgENvMitTolc7rKRkgPMQZnqUtgelwrz7vG82ruRm713u7tOZdxDQoEkzpzwhIWaUz9Knn0nCQGCn9BBqrJ3oMqcOmyGQtUZPfPUQX4hSLBurzsYX1ZFsxNR4vUj8y1i6TDkn+btiD6VB3b6ZDInrV4N4i24wH2vm3wCD8OZ/8WTuPU7CEBKUQ1iHl+NyrNyxZcH0tyjBaHo6lQTVZWknmn+qLrhFJxplvhhT1t9ilZJIhyri5iSLNj7A8CS1n4OxBNdYWN+7y7q/W58QIXoDnlpt8v3bsPBIEfp9Pd3S8s6ZJYVrESCSGHGx+J3mehbVle73bDQP</ds:X509Certificate>
                    </ds:X509Data>
                  </ds:KeyInfo>
                </ds:Signature>
              </ext:ExtensionContent>
            </ext:UBLExtension>
          </ext:UBLExtensions>
          <!-- GENERADO DESDE WWW.NUBEFACT.COM -->
          <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
          <cbc:CustomizationID>2.0</cbc:CustomizationID>
          <cbc:ID>F003-2</cbc:ID>
          <cbc:IssueDate>2023-07-12</cbc:IssueDate>
          <cbc:IssueTime>16:42:08</cbc:IssueTime>
          <cbc:InvoiceTypeCode listID="0101" listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" name="Tipo de Operacion" listSchemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51">01</cbc:InvoiceTypeCode>
          <cbc:Note languageLocaleID="1000">CIENTO CATORCE CON 00/100 SOLES</cbc:Note>
          <cbc:Note>Obs: Orden: OV-000360</cbc:Note>
          <cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listAgencyName="United Nations Economic Commission for Europe" listName="Currency">PEN</cbc:DocumentCurrencyCode>
          <cac:Signature>
            <cbc:ID>10706135311</cbc:ID>
            <cbc:Note>WWW.NUBEFACT.COM</cbc:Note>
            <cac:SignatoryParty>
              <cac:PartyIdentification>
                <cbc:ID>10706135311</cbc:ID>
              </cac:PartyIdentification>
              <cac:PartyName>
                <cbc:Name>CASTILLO LOAYZA JULIA JANETH SARA</cbc:Name>
              </cac:PartyName>
            </cac:SignatoryParty>
            <cac:DigitalSignatureAttachment>
              <cac:ExternalReference>
                <cbc:URI>10706135311</cbc:URI>
              </cac:ExternalReference>
            </cac:DigitalSignatureAttachment>
          </cac:Signature>
          <cac:AccountingSupplierParty>
            <cac:Party>
              <cac:PartyIdentification>
                <cbc:ID schemeID="6" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">10706135311</cbc:ID>
              </cac:PartyIdentification>
              <cac:PartyName>
                <cbc:Name>CASTILLO LOAYZA JULIA JANETH SARA</cbc:Name>
              </cac:PartyName>
              <cac:PartyLegalEntity>
                <cbc:RegistrationName>CASTILLO LOAYZA JULIA JANETH SARA</cbc:RegistrationName>
                <cac:RegistrationAddress>
                  <cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">000000</cbc:ID>
                  <cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">0000</cbc:AddressTypeCode>
                  <cbc:CityName>-</cbc:CityName>
                  <cbc:CountrySubentity>-</cbc:CountrySubentity>
                  <cbc:District>-</cbc:District>
                  <cac:AddressLine>
                    <cbc:Line>-</cbc:Line>
                  </cac:AddressLine>
                  <cac:Country>
                    <cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country">PE</cbc:IdentificationCode>
                  </cac:Country>
                </cac:RegistrationAddress>
              </cac:PartyLegalEntity>
            </cac:Party>
          </cac:AccountingSupplierParty>
          <cac:AccountingCustomerParty>
            <cac:Party>
              <cac:PartyIdentification>
                <cbc:ID schemeID="6" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">20109072177</cbc:ID>
              </cac:PartyIdentification>
              <cac:PartyLegalEntity>
                <cbc:RegistrationName>CENCOSUD RETAIL PERU S.A.</cbc:RegistrationName>
                <cac:RegistrationAddress>
                  <cac:AddressLine>
                    <cbc:Line>CAL. AUGUSTO ANGULO NRO. 130 URB. SAN ANTONIO - LIMA LIMA MIRAFLORES</cbc:Line>
                  </cac:AddressLine>
                </cac:RegistrationAddress>
              </cac:PartyLegalEntity>
            </cac:Party>
          </cac:AccountingCustomerParty>
          <cac:PaymentTerms>
            <cbc:ID>FormaPago</cbc:ID>
            <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
          </cac:PaymentTerms>
          <cac:AllowanceCharge>
            <cbc:ChargeIndicator>true</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode listAgencyName="PE:SUNAT" listName="Cargo/descuento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo53">50</cbc:AllowanceChargeReasonCode>
            <cbc:MultiplierFactorNumeric>0.11511</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID="PEN">11.32</cbc:Amount>
            <cbc:BaseAmount currencyID="PEN">98.34</cbc:BaseAmount>
          </cac:AllowanceCharge>
          <cac:TaxTotal>
            <cbc:TaxAmount currencyID="PEN">15.66</cbc:TaxAmount>
            <cac:TaxSubtotal>
              <cbc:TaxableAmount currencyID="PEN">87.02</cbc:TaxableAmount>
              <cbc:TaxAmount currencyID="PEN">15.66</cbc:TaxAmount>
              <cac:TaxCategory>
                <cac:TaxScheme>
                  <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                  <cbc:Name>IGV</cbc:Name>
                  <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
              </cac:TaxCategory>
            </cac:TaxSubtotal>
          </cac:TaxTotal>
          <cac:LegalMonetaryTotal>
            <cbc:LineExtensionAmount currencyID="PEN">87.02</cbc:LineExtensionAmount>
            <cbc:TaxInclusiveAmount currencyID="PEN">102.68</cbc:TaxInclusiveAmount>
            <cbc:AllowanceTotalAmount currencyID="PEN">0.00</cbc:AllowanceTotalAmount>
            <cbc:ChargeTotalAmount currencyID="PEN">11.32</cbc:ChargeTotalAmount>
            <cbc:PrepaidAmount currencyID="PEN">0.00</cbc:PrepaidAmount>
            <cbc:PayableAmount currencyID="PEN">114.00</cbc:PayableAmount>
          </cac:LegalMonetaryTotal>
          <!-- GENERADO DESDE WWW.NUBEFACT.COM -->
          <cac:InvoiceLine>
            <cbc:ID>1</cbc:ID>
            <cbc:InvoicedQuantity unitCode="NIU" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">1.0</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="PEN">61.07</cbc:LineExtensionAmount>
            <cac:PricingReference>
              <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="PEN">72.061</cbc:PriceAmount>
                <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
              </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
              <cbc:TaxAmount currencyID="PEN">10.99</cbc:TaxAmount>
              <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="PEN">61.07</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="PEN">10.99</cbc:TaxAmount>
                <cac:TaxCategory>
                  <cbc:Percent>18.00</cbc:Percent>
                  <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">10</cbc:TaxExemptionReasonCode>
                  <cac:TaxScheme>
                    <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                  </cac:TaxScheme>
                </cac:TaxCategory>
              </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
              <cbc:Description>CARROTCAKE</cbc:Description>
              <cac:SellersItemIdentification>
                <cbc:ID>IDC46</cbc:ID>
              </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
              <cbc:PriceAmount currencyID="PEN">61.069</cbc:PriceAmount>
            </cac:Price>
          </cac:InvoiceLine>
          <cac:InvoiceLine>
            <cbc:ID>2</cbc:ID>
            <cbc:InvoicedQuantity unitCode="NIU" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">1.0</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="PEN">3.05</cbc:LineExtensionAmount>
            <cac:PricingReference>
              <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="PEN">3.603</cbc:PriceAmount>
                <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
              </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
              <cbc:TaxAmount currencyID="PEN">0.55</cbc:TaxAmount>
              <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="PEN">3.05</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="PEN">0.55</cbc:TaxAmount>
                <cac:TaxCategory>
                  <cbc:Percent>18.00</cbc:Percent>
                  <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">10</cbc:TaxExemptionReasonCode>
                  <cac:TaxScheme>
                    <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                  </cac:TaxScheme>
                </cac:TaxCategory>
              </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
              <cbc:Description>BROWNIE</cbc:Description>
              <cac:SellersItemIdentification>
                <cbc:ID>IDC7</cbc:ID>
              </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
              <cbc:PriceAmount currencyID="PEN">3.053</cbc:PriceAmount>
            </cac:Price>
          </cac:InvoiceLine>
          <cac:InvoiceLine>
            <cbc:ID>3</cbc:ID>
            <cbc:InvoicedQuantity unitCode="NIU" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">1.0</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="PEN">3.82</cbc:LineExtensionAmount>
            <cac:PricingReference>
              <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="PEN">4.504</cbc:PriceAmount>
                <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
              </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
              <cbc:TaxAmount currencyID="PEN">0.69</cbc:TaxAmount>
              <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="PEN">3.82</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="PEN">0.69</cbc:TaxAmount>
                <cac:TaxCategory>
                  <cbc:Percent>18.00</cbc:Percent>
                  <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">10</cbc:TaxExemptionReasonCode>
                  <cac:TaxScheme>
                    <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                  </cac:TaxScheme>
                </cac:TaxCategory>
              </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
              <cbc:Description>ALFAJORES</cbc:Description>
              <cac:SellersItemIdentification>
                <cbc:ID>IDC2</cbc:ID>
              </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
              <cbc:PriceAmount currencyID="PEN">3.817</cbc:PriceAmount>
            </cac:Price>
          </cac:InvoiceLine>
          <cac:InvoiceLine>
            <cbc:ID>4</cbc:ID>
            <cbc:InvoicedQuantity unitCode="NIU" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">1.0</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="PEN">19.08</cbc:LineExtensionAmount>
            <cac:PricingReference>
              <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="PEN">22.519</cbc:PriceAmount>
                <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
              </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
              <cbc:TaxAmount currencyID="PEN">3.44</cbc:TaxAmount>
              <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="PEN">19.08</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="PEN">3.44</cbc:TaxAmount>
                <cac:TaxCategory>
                  <cbc:Percent>18.00</cbc:Percent>
                  <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">10</cbc:TaxExemptionReasonCode>
                  <cac:TaxScheme>
                    <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                  </cac:TaxScheme>
                </cac:TaxCategory>
              </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
              <cbc:Description>BOLA DE HELADO SABOR FRESA</cbc:Description>
              <cac:SellersItemIdentification>
                <cbc:ID>v3gllmUtKlsgTHao</cbc:ID>
              </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
              <cbc:PriceAmount currencyID="PEN">19.084</cbc:PriceAmount>
            </cac:Price>
          </cac:InvoiceLine>
          <!-- GENERADO DESDE WWW.NUBEFACT.COM -->
        </Invoice>
        ';
        $file2 = '<?xml version="1.0" encoding="UTF-8"?>
        <Invoice xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">
          <!-- GENERADO DESDE WWW.NUBEFACT.COM -->
          <ext:UBLExtensions>
            <ext:UBLExtension>
              <ext:ExtensionContent>
                <ds:Signature Id="NubeFacTSign">
                  <ds:SignedInfo>
                    <ds:CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/>
                    <ds:SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/>
                    <ds:Reference URI="">
                      <ds:Transforms>
                        <ds:Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/>
                      </ds:Transforms>
                      <ds:DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/>
                      <ds:DigestValue>IfmVqW/0GS1Jui0S1R816Qs261FiySxrDXWQc64OPDo=</ds:DigestValue>
                    </ds:Reference>
                  </ds:SignedInfo>
                  <ds:SignatureValue>Uo+5sHI5MIhAC84uRVYmFmLbfw3zh6GsD3iqXK4w+l4KZFx3CJdBtnjixwGt51QEPfviSjxW9Uoa6rnxqdpW2lZB0zZS7mX0uMeQXuaMSC9Ih3z82cAIuVfJPlBXifjg3MF8TcrjjyqKo1b45m1vuFW8AvaqwHsKHhyDCGYr6hvO0U+COk1tkIq+863qyp2mnMnmiEIebb0tU77kPHoA44Z25A5j9MQ+KK+uTB7f7xD5UuZM2ZhnUXz1HuD1u6kbVOnOVGxjgu4vP+X2+7gRb/yXQMtrDWxw8cdOlWjUMeXADnsRYmPgnl8fgpGVVgE+6bY54gMIliLzYujl6LCwUw==</ds:SignatureValue>
                  <ds:KeyInfo>
                    <ds:X509Data>
                      <ds:X509SubjectName>DC=LLAMA.PE SA,C=PE,ST=LIMA,L=LIMA,O=TU EMPRESA S.A.,OU=DNI 9999999 RUC 20600695771 - CERTIFICADO PARA DEMOSTRACI\xC3\x93N,CN=NOMBRE REPRESENTANTE LEGAL - CERTIFICADO PARA DEMOSTRACI\xC3\x93N,emailAddress=demo@llama.pe</ds:X509SubjectName>
                      <ds:X509Certificate>MIIE9zCCA9+gAwIBAgIIcTdHJpbkFzcwDQYJKoZIhvcNAQEFBQAwggENMRswGQYKCZImiZPyLGQBGRYLTExBTUEuUEUgU0ExCzAJBgNVBAYTAlBFMQ0wCwYDVQQIDARMSU1BMQ0wCwYDVQQHDARMSU1BMRgwFgYDVQQKDA9UVSBFTVBSRVNBIFMuQS4xRTBDBgNVBAsMPEROSSA5OTk5OTk5IFJVQyAyMDYwMDY5NTc3MSAtIENFUlRJRklDQURPIFBBUkEgREVNT1NUUkFDScOTTjFEMEIGA1UEAww7Tk9NQlJFIFJFUFJFU0VOVEFOVEUgTEVHQUwgLSBDRVJUSUZJQ0FETyBQQVJBIERFTU9TVFJBQ0nDk04xHDAaBgkqhkiG9w0BCQEWDWRlbW9AbGxhbWEucGUwHhcNMjAwODMxMTY0MzA4WhcNMjIwODMxMTY0MzA4WjCCAQ0xGzAZBgoJkiaJk/IsZAEZFgtMTEFNQS5QRSBTQTELMAkGA1UEBhMCUEUxDTALBgNVBAgMBExJTUExDTALBgNVBAcMBExJTUExGDAWBgNVBAoMD1RVIEVNUFJFU0EgUy5BLjFFMEMGA1UECww8RE5JIDk5OTk5OTkgUlVDIDIwNjAwNjk1NzcxIC0gQ0VSVElGSUNBRE8gUEFSQSBERU1PU1RSQUNJw5NOMUQwQgYDVQQDDDtOT01CUkUgUkVQUkVTRU5UQU5URSBMRUdBTCAtIENFUlRJRklDQURPIFBBUkEgREVNT1NUUkFDScOTTjEcMBoGCSqGSIb3DQEJARYNZGVtb0BsbGFtYS5wZTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAPdMhCKhkVckQBweJf9cxKCVNIi5ggC2F5qcPgET2ejSPA2EjCE4gms4UqhRF9G5fumkNotHlgXxFoqrYRxIz8y6V1cTh6+D3/487lslSIgz6J4712AKBmL9d24lfMLD8CvZ9WPT4M4A67KLUZrfgZh8/SrdJzpXfOdhNfXTopMlsv2Cy3wxvZQ+gKheTRUOjFa99YGkjoJ8fzDiHzh+j72hTDZ2kTyO6Cz8vPcFYSVAXWlLYj4ZW23tml5+rT9ZnBGvpPPC8cGveVVdcHQt/4mvavmrLbNw6yZwYv2pJ6vOq0m28/ur+6bneVj0jmb2YaBnh3qcbfOBaqzIWPpvHg8CAwEAAaNXMFUwHQYDVR0OBBYEFAjmmpTDRPV+Ws2x4z4ujUKJXf56MB8GA1UdIwQYMBaAFAjmmpTDRPV+Ws2x4z4ujUKJXf56MBMGA1UdJQQMMAoGCCsGAQUFBwMBMA0GCSqGSIb3DQEBBQUAA4IBAQDr/i5a5IN1U8DKgENvMitTolc7rKRkgPMQZnqUtgelwrz7vG82ruRm713u7tOZdxDQoEkzpzwhIWaUz9Knn0nCQGCn9BBqrJ3oMqcOmyGQtUZPfPUQX4hSLBurzsYX1ZFsxNR4vUj8y1i6TDkn+btiD6VB3b6ZDInrV4N4i24wH2vm3wCD8OZ/8WTuPU7CEBKUQ1iHl+NyrNyxZcH0tyjBaHo6lQTVZWknmn+qLrhFJxplvhhT1t9ilZJIhyri5iSLNj7A8CS1n4OxBNdYWN+7y7q/W58QIXoDnlpt8v3bsPBIEfp9Pd3S8s6ZJYVrESCSGHGx+J3mehbVle73bDQP</ds:X509Certificate>
                    </ds:X509Data>
                  </ds:KeyInfo>
                </ds:Signature>
              </ext:ExtensionContent>
            </ext:UBLExtension>
          </ext:UBLExtensions>
          <!-- GENERADO DESDE WWW.NUBEFACT.COM -->
          <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
          <cbc:CustomizationID>2.0</cbc:CustomizationID>
          <cbc:ID>F003-1</cbc:ID>
          <cbc:IssueDate>2023-07-12</cbc:IssueDate>
          <cbc:IssueTime>16:44:21</cbc:IssueTime>
          <cbc:InvoiceTypeCode listID="0101" listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" name="Tipo de Operacion" listSchemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51">01</cbc:InvoiceTypeCode>
          <cbc:Note languageLocaleID="1000">CINCUENTA Y SIETE CON 50/100 SOLES</cbc:Note>
          <cbc:Note>Obs: Orden: OV-000361</cbc:Note>
          <cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listAgencyName="United Nations Economic Commission for Europe" listName="Currency">PEN</cbc:DocumentCurrencyCode>
          <cac:Signature>
            <cbc:ID>10706135311</cbc:ID>
            <cbc:Note>WWW.NUBEFACT.COM</cbc:Note>
            <cac:SignatoryParty>
              <cac:PartyIdentification>
                <cbc:ID>10706135311</cbc:ID>
              </cac:PartyIdentification>
              <cac:PartyName>
                <cbc:Name>CASTILLO LOAYZA JULIA JANETH SARA</cbc:Name>
              </cac:PartyName>
            </cac:SignatoryParty>
            <cac:DigitalSignatureAttachment>
              <cac:ExternalReference>
                <cbc:URI>10706135311</cbc:URI>
              </cac:ExternalReference>
            </cac:DigitalSignatureAttachment>
          </cac:Signature>
          <cac:AccountingSupplierParty>
            <cac:Party>
              <cac:PartyIdentification>
                <cbc:ID schemeID="6" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">10706135311</cbc:ID>
              </cac:PartyIdentification>
              <cac:PartyName>
                <cbc:Name>CASTILLO LOAYZA JULIA JANETH SARA</cbc:Name>
              </cac:PartyName>
              <cac:PartyLegalEntity>
                <cbc:RegistrationName>CASTILLO LOAYZA JULIA JANETH SARA</cbc:RegistrationName>
                <cac:RegistrationAddress>
                  <cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">000000</cbc:ID>
                  <cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">0000</cbc:AddressTypeCode>
                  <cbc:CityName>-</cbc:CityName>
                  <cbc:CountrySubentity>-</cbc:CountrySubentity>
                  <cbc:District>-</cbc:District>
                  <cac:AddressLine>
                    <cbc:Line>-</cbc:Line>
                  </cac:AddressLine>
                  <cac:Country>
                    <cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country">PE</cbc:IdentificationCode>
                  </cac:Country>
                </cac:RegistrationAddress>
              </cac:PartyLegalEntity>
            </cac:Party>
          </cac:AccountingSupplierParty>
          <cac:AccountingCustomerParty>
            <cac:Party>
              <cac:PartyIdentification>
                <cbc:ID schemeID="6" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">20535814121</cbc:ID>
              </cac:PartyIdentification>
              <cac:PartyLegalEntity>
                <cbc:RegistrationName>CORPORACION TURISTICA HOTELERA PERUANA SOCIEDAD ANONIMA CERRADA - CORPORACION THP S.A.C.</cbc:RegistrationName>
                <cac:RegistrationAddress>
                  <cac:AddressLine>
                    <cbc:Line>CAL. PHILIP VON LEONARD NRO. 224 DPTO. 402 URB. CORPAC - LIMA LIMA SAN BORJA</cbc:Line>
                  </cac:AddressLine>
                </cac:RegistrationAddress>
              </cac:PartyLegalEntity>
            </cac:Party>
          </cac:AccountingCustomerParty>
          <cac:PaymentTerms>
            <cbc:ID>FormaPago</cbc:ID>
            <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
          </cac:PaymentTerms>
          <cac:AllowanceCharge>
            <cbc:ChargeIndicator>true</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode listAgencyName="PE:SUNAT" listName="Cargo/descuento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo53">50</cbc:AllowanceChargeReasonCode>
            <cbc:MultiplierFactorNumeric>0.11518</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID="PEN">5.76</cbc:Amount>
            <cbc:BaseAmount currencyID="PEN">50.01</cbc:BaseAmount>
          </cac:AllowanceCharge>
          <cac:TaxTotal>
            <cbc:TaxAmount currencyID="PEN">7.49</cbc:TaxAmount>
            <cac:TaxSubtotal>
              <cbc:TaxableAmount currencyID="PEN">41.60</cbc:TaxableAmount>
              <cbc:TaxAmount currencyID="PEN">7.49</cbc:TaxAmount>
              <cac:TaxCategory>
                <cac:TaxScheme>
                  <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                  <cbc:Name>IGV</cbc:Name>
                  <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
              </cac:TaxCategory>
            </cac:TaxSubtotal>
            <cac:TaxSubtotal>
              <cbc:TaxableAmount currencyID="PEN">2.65</cbc:TaxableAmount>
              <cbc:TaxAmount currencyID="PEN">0.00</cbc:TaxAmount>
              <cac:TaxCategory>
                <cac:TaxScheme>
                  <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">9997</cbc:ID>
                  <cbc:Name>EXO</cbc:Name>
                  <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
              </cac:TaxCategory>
            </cac:TaxSubtotal>
          </cac:TaxTotal>
          <cac:LegalMonetaryTotal>
            <cbc:LineExtensionAmount currencyID="PEN">44.25</cbc:LineExtensionAmount>
            <cbc:TaxInclusiveAmount currencyID="PEN">51.74</cbc:TaxInclusiveAmount>
            <cbc:AllowanceTotalAmount currencyID="PEN">0.00</cbc:AllowanceTotalAmount>
            <cbc:ChargeTotalAmount currencyID="PEN">5.76</cbc:ChargeTotalAmount>
            <cbc:PrepaidAmount currencyID="PEN">0.00</cbc:PrepaidAmount>
            <cbc:PayableAmount currencyID="PEN">57.50</cbc:PayableAmount>
          </cac:LegalMonetaryTotal>
          <!-- GENERADO DESDE WWW.NUBEFACT.COM -->
          <cac:InvoiceLine>
            <cbc:ID>1</cbc:ID>
            <cbc:InvoicedQuantity unitCode="NIU" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">1.0</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="PEN">38.17</cbc:LineExtensionAmount>
            <cac:PricingReference>
              <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="PEN">45.038</cbc:PriceAmount>
                <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
              </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
              <cbc:TaxAmount currencyID="PEN">6.87</cbc:TaxAmount>
              <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="PEN">38.17</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="PEN">6.87</cbc:TaxAmount>
                <cac:TaxCategory>
                  <cbc:Percent>18.00</cbc:Percent>
                  <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">10</cbc:TaxExemptionReasonCode>
                  <cac:TaxScheme>
                    <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                  </cac:TaxScheme>
                </cac:TaxCategory>
              </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
              <cbc:Description>PITIPANES</cbc:Description>
              <cac:SellersItemIdentification>
                <cbc:ID>IDC30</cbc:ID>
              </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
              <cbc:PriceAmount currencyID="PEN">38.168</cbc:PriceAmount>
            </cac:Price>
          </cac:InvoiceLine>
          <cac:InvoiceLine>
            <cbc:ID>2</cbc:ID>
            <cbc:InvoicedQuantity unitCode="NIU" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">1.0</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="PEN">0.38</cbc:LineExtensionAmount>
            <cac:PricingReference>
              <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="PEN">0.45</cbc:PriceAmount>
                <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
              </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
              <cbc:TaxAmount currencyID="PEN">0.07</cbc:TaxAmount>
              <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="PEN">0.38</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="PEN">0.07</cbc:TaxAmount>
                <cac:TaxCategory>
                  <cbc:Percent>18.00</cbc:Percent>
                  <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">10</cbc:TaxExemptionReasonCode>
                  <cac:TaxScheme>
                    <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                  </cac:TaxScheme>
                </cac:TaxCategory>
              </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
              <cbc:Description>MINI CHOCOLATES</cbc:Description>
              <cac:SellersItemIdentification>
                <cbc:ID>IDC26</cbc:ID>
              </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
              <cbc:PriceAmount currencyID="PEN">0.382</cbc:PriceAmount>
            </cac:Price>
          </cac:InvoiceLine>
          <cac:InvoiceLine>
            <cbc:ID>3</cbc:ID>
            <cbc:InvoicedQuantity unitCode="NIU" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">1.0</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="PEN">2.29</cbc:LineExtensionAmount>
            <cac:PricingReference>
              <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="PEN">2.702</cbc:PriceAmount>
                <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
              </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
              <cbc:TaxAmount currencyID="PEN">0.41</cbc:TaxAmount>
              <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="PEN">2.29</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="PEN">0.41</cbc:TaxAmount>
                <cac:TaxCategory>
                  <cbc:Percent>18.00</cbc:Percent>
                  <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">10</cbc:TaxExemptionReasonCode>
                  <cac:TaxScheme>
                    <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                  </cac:TaxScheme>
                </cac:TaxCategory>
              </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
              <cbc:Description>TRUFAS</cbc:Description>
              <cac:SellersItemIdentification>
                <cbc:ID>IDC41</cbc:ID>
              </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
              <cbc:PriceAmount currencyID="PEN">2.29</cbc:PriceAmount>
            </cac:Price>
          </cac:InvoiceLine>
          <cac:InvoiceLine>
            <cbc:ID>4</cbc:ID>
            <cbc:InvoicedQuantity unitCode="NIU" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">1.0</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="PEN">2.66</cbc:LineExtensionAmount>
            <cac:PricingReference>
              <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="PEN">2.655</cbc:PriceAmount>
                <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
              </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
              <cbc:TaxAmount currencyID="PEN">0.00</cbc:TaxAmount>
              <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="PEN">2.66</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="PEN">0.00</cbc:TaxAmount>
                <cac:TaxCategory>
                  <cbc:Percent>0.00</cbc:Percent>
                  <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">20</cbc:TaxExemptionReasonCode>
                  <cac:TaxScheme>
                    <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">9997</cbc:ID>
                    <cbc:Name>EXO</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                  </cac:TaxScheme>
                </cac:TaxCategory>
              </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
              <cbc:Description>AGUA CIELO 300ML</cbc:Description>
              <cac:SellersItemIdentification>
                <cbc:ID>IDC1</cbc:ID>
              </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
              <cbc:PriceAmount currencyID="PEN">2.655</cbc:PriceAmount>
            </cac:Price>
          </cac:InvoiceLine>
          <cac:InvoiceLine>
            <cbc:ID>5</cbc:ID>
            <cbc:InvoicedQuantity unitCode="NIU" unitCodeListID="UN/ECE rec 20" unitCodeListAgencyName="United Nations Economic Commission for Europe">1.0</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="PEN">0.76</cbc:LineExtensionAmount>
            <cac:PricingReference>
              <cac:AlternativeConditionPrice>
                <cbc:PriceAmount currencyID="PEN">0.901</cbc:PriceAmount>
                <cbc:PriceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Precio" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">01</cbc:PriceTypeCode>
              </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
              <cbc:TaxAmount currencyID="PEN">0.14</cbc:TaxAmount>
              <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="PEN">0.76</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="PEN">0.14</cbc:TaxAmount>
                <cac:TaxCategory>
                  <cbc:Percent>18.00</cbc:Percent>
                  <cbc:TaxExemptionReasonCode listAgencyName="PE:SUNAT" listName="Afectacion del IGV" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo07">10</cbc:TaxExemptionReasonCode>
                  <cac:TaxScheme>
                    <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                  </cac:TaxScheme>
                </cac:TaxCategory>
              </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
              <cbc:Description>BONOBON</cbc:Description>
              <cac:SellersItemIdentification>
                <cbc:ID>IDC5</cbc:ID>
              </cac:SellersItemIdentification>
            </cac:Item>
            <cac:Price>
              <cbc:PriceAmount currencyID="PEN">0.763</cbc:PriceAmount>
            </cac:Price>
          </cac:InvoiceLine>
          <!-- GENERADO DESDE WWW.NUBEFACT.COM -->
        </Invoice>
        ';
        $file3 = '<?xml version="1.0" encoding="UTF-8"?>
        <product>
            <name>Producto A</name>
            <description>Descripción del Producto A</description>
            <price>10.99</price>
        </product>';
        try {
            $xmlFiles = $request->file('files');

            if (!is_array($xmlFiles)) {
                $xmlFiles = [$xmlFiles];
            }


            $xmlContents = [ $file1, $file2, $file3 ];
            // foreach ($xmlFiles as $xmlFile) {
            //     $xmlContents[] = file_get_contents($xmlFile->getRealPath());
            // }

            $user = auth()->user();

            // $vouchers = $this->voucherService->storeVouchersFromXmlContents($xmlContents, $user);
            VoucherRegisterJob::dispatch($xmlContents, $user);

            return response([
                // 'data' => VoucherResource::collection($vouchers),
                'message' => 'Vouchers are being processed we will notify you when they are ready',
            ], 201);
        } catch (Exception $exception) {
            return response([
                'message' => $exception->getMessage(),
            ], 400);
        }
    }
}
