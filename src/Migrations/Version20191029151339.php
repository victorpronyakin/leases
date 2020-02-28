<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191029151339 extends AbstractMigration
{

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('TRUNCATE TABLE currency');

        $sql = "INSERT currency (code, name) VALUES";

        $currencyItem = [
            'USD' => 'US dollar (USD)',
            'EUR' => 'Euro (EUR)',
            'JPY' => 'Japanese yen (JPY)',
            'GBP' => 'Pound sterling (GBP)',
            'AUD' => 'Australian dollar (AUD)',
            'CAD' => 'Canadian dollar (CAD)',
            'CHF' => 'Swiss franc (CHF)',
            'CNH' => 'Chinese renminbi (CNH)',
            'SEK' => 'Swedish krona (SEK)',
            'NZD' => 'New Zealand dollar (NZD)',
            'DZD' => 'Algerian dinar (DZD)',
            'AOA' => 'Angolan kwanza (AOA)',
            'BWP' => 'Botswana pula (BWP)',
            'BIF' => 'Burundi franc (BIF)',
            'CVE' => 'Cape Verdean escudo (CVE)',
            'XAF' => 'CFA franc (XAF)',
            'XOF' => 'CFA franc (XOF)',
            'KMF' => 'Comorian franc (KMF)',
            'CDF' => 'Congolese franc (CDF)',
            'GMD' => 'Dalasi (GMD)',
            'DJF' => 'Djiboutian franc (GMD)',
            'EGP' => 'Egyptian pound (EGP)',
            'ERN' => 'Eritrean nakfa (ERN)',
            'ETB' => 'Ethiopian birr (ETB)',
            'GHS' => 'Ghanaian cedi (GHS)',
            'GNF' => 'Guinean franc (GNF)',
            'KES' => 'Kenyan shilling (KES)',
            'LSL' => 'Lesotho loti (LSL)',
            'LRD' => 'Liberian dollar (LRD)',
            'LYD' => 'Libyan dinar (LYD)',
            'MGA' => 'Malagasy ariary (MGA)',
            'MWK' => 'Malawian kwacha (MWK)',
            'MUR' => 'Mauritian rupee (MUR)',
            'MAD' => 'Moroccan dirham (MAD)',
            'MZN' => 'Mozambican metical (MZN)',
            'NAD' => 'Namibian dollar (NAD)',
            'NGN' => 'Nigerian naira (NGN)',
            'MRO' => 'Ouguiya (MRO)',
            'ZBN' => 'RTGS Dollar (ZBN)',
            'RWF' => 'Rwandan franc (RWF)',
            'STD' => 'São Tomé and Príncipe dobra (STD)',
            'SCR' => 'Seychellois rupee (SCR)',
            'SLL' => 'Sierra Leonean leone (SLL)',
            'SOS' => 'Somali shilling (SOS)',
            'ZAR' => 'South African rand (ZAR)',
            'SSP' => 'South Sudanese pound (SSP)',
            'SDG' => 'Sudanese pound (SDG)',
            'SZL' => 'Swazi lilangeni (SZL)',
            'TZS' => 'Tanzanian shilling (TZS)',
            'TND' => 'Tunisian dinar (TND)',
            'UGX' => 'Ugandan shilling (UGX)',
            'ZMW' => 'Zambian kwacha (ZMW)',
        ];

        foreach($currencyItem as $code=>$name){
            $sql .= " ('$code', '$name'),";
        }
        $sql = substr($sql, 0, -1);
        $sql .= ';';

        $this->addSql($sql);
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('TRUNCATE TABLE currency');
    }
}
