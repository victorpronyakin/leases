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
final class Version20200128140159 extends AbstractMigration implements ContainerAwareInterface
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
            'USD' => '$',
            'EUR' => '€',
            'JPY' => '¥',
            'GBP' => '£',
            'AUD' => 'A$',
            'CAD' => 'C$',
            'CHF' => '₣',
            'CNH' => '¥',
            'SEK' => 'kr',
            'NZD' => 'NZ$',
            'DZD' => '.د.ج',
            'AOA' => 'Kz',
            'BWP' => 'P',
            'BIF' => '₣',
            'CVE' => '$',
            'XAF' => '₣',
            'XOF' => '₣',
            'KMF' => '₣',
            'CDF' => '₣',
            'GMD' => 'D',
            'DJF' => '₣',
            'EGP' => 'E£',
            'ERN' => 'Nfk',
            'ETB' => 'Br',
            'GHS' => '₵',
            'GNF' => '‎₣',
            'KES' => 'KSh',
            'LSL' => 'LSL',
            'LRD' => 'L$',
            'LYD' => '.د.ل',
            'MGA' => 'Ar',
            'MWK' => 'K',
            'MUR' => '₨',
            'MAD' => 'درهم‎',
            'MZN' => 'MT',
            'NAD' => 'N$',
            'NGN' => '₦',
            'MRO' => 'أوقية',
            'ZBN' => 'RTGS$',
            'RWF' => '‎₣',
            'STD' => 'Db',
            'SCR' => 'SR',
            'SLL' => 'Le',
            'SOS' => 'S',
            'ZAR' => 'R',
            'SSP' => '£',
            'SDG' => '£',
            'SZL' => 'E',
            'TZS' => 'TSh',
            'TND' => 'د.ت',
            'UGX' => 'USh',
            'ZMW' => 'K',
        ];

        $em = $this->container->get('doctrine.orm.entity_manager');
        $currencies = $em->getRepository(Currency::class)->findAll();

        foreach($currencies as $currency){
            if($currency instanceof Currency){
                $currency->setSymbol((isset($currencyItem[$currency->getCode()])) ? $currencyItem[$currency->getCode()] : null);
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
