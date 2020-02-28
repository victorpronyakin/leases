<?php


namespace App\Service;


use App\Entity\Currency;
use App\Entity\Setting;
use Doctrine\ORM\EntityManagerInterface;

class SettingService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * CurrencyService constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $name
     * @param bool $json
     * @return mixed|string|null
     */
    public function getValue($name, $json=false){
        $value = null;
        $setting = $this->em->getRepository(Setting::class)->findOneBy(['name'=>$name]);
        if($setting instanceof Setting){
            if($json){
                $value = json_decode($setting->getValue(), true);
            }
            else{
                $value = $setting->getValue();
            }
        }

        return $value;
    }

    /**
     * @param $name
     * @param $value
     * @param bool $json
     */
    public function setValue($name, $value, $json=false){
        $setting = $this->em->getRepository(Setting::class)->findOneBy(['name' => $name]);
        if($json){
            $value = json_encode($value);
        }
        if ($setting instanceof Setting) {
            $setting->setValue($value);
        } else {
            $setting = new Setting($name, $value);
        }

        $this->em->persist($setting);
        $this->em->flush();
    }


    /**
     * @return mixed|null
     */
    public function getCurrencySymbol(){
        $symbol = null;
        $code = $this->getValue('currency');
        if(!empty($code)){
            $currency = $this->em->getRepository(Currency::class)->findOneBy(['code'=>$code]);
            if($currency instanceof Currency){
                $symbol = $currency->getSymbol();
            }
        }

        return $symbol;
    }
}
