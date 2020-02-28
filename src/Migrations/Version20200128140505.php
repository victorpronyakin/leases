<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Currency;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200128140505 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $currencyItem = [
            'USD' => 'US dollar',
            'EUR' => 'Euro',
            'JPY' => 'Japanese yen',
            'GBP' => 'Pound sterling',
            'AUD' => 'Australian dollar',
            'CAD' => 'Canadian dollar',
            'CHF' => 'Swiss franc',
            'CNH' => 'Chinese renminbi',
            'SEK' => 'Swedish krona',
            'NZD' => 'New Zealand dollar',
            'DZD' => 'Algerian dinar',
            'AOA' => 'Angolan kwanza',
            'BWP' => 'Botswana pula',
            'BIF' => 'Burundi franc',
            'CVE' => 'Cape Verdean escudo',
            'XAF' => 'CFA franc',
            'XOF' => 'CFA franc',
            'KMF' => 'Comorian franc',
            'CDF' => 'Congolese franc',
            'GMD' => 'Dalasi',
            'DJF' => 'Djiboutian franc',
            'EGP' => 'Egyptian pound',
            'ERN' => 'Eritrean nakfa',
            'ETB' => 'Ethiopian birr',
            'GHS' => 'Ghanaian cedi',
            'GNF' => 'Guinean franc',
            'KES' => 'Kenyan shilling',
            'LSL' => 'Lesotho loti',
            'LRD' => 'Liberian dollar',
            'LYD' => 'Libyan dinar',
            'MGA' => 'Malagasy ariary',
            'MWK' => 'Malawian kwacha',
            'MUR' => 'Mauritian rupee',
            'MAD' => 'Moroccan dirham',
            'MZN' => 'Mozambican metical',
            'NAD' => 'Namibian dollar',
            'NGN' => 'Nigerian naira',
            'MRO' => 'Ouguiya',
            'ZBN' => 'RTGS Dollar',
            'RWF' => 'Rwandan franc',
            'STD' => 'São Tomé and Príncipe dobra',
            'SCR' => 'Seychellois rupee',
            'SLL' => 'Sierra Leonean leone',
            'SOS' => 'Somali shilling',
            'ZAR' => 'South African rand',
            'SSP' => 'South Sudanese pound',
            'SDG' => 'Sudanese pound',
            'SZL' => 'Swazi lilangeni',
            'TZS' => 'Tanzanian shilling',
            'TND' => 'Tunisian dinar',
            'UGX' => 'Ugandan shilling',
            'ZMW' => 'Zambian kwacha',
        ];

        $em = $this->container->get('doctrine.orm.entity_manager');
        $currencies = $em->getRepository(Currency::class)->findAll();

        foreach($currencies as $currency){
            if($currency instanceof Currency){
                $currency->setName((isset($currencyItem[$currency->getCode()])) ? $currencyItem[$currency->getCode()] : null);
                $em->persist($currency);
            }
        }
        $em->flush();

        $this->addSql('SELECT 1');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
