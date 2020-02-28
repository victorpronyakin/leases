<?php


namespace App\Menu;

use App\Helper\UserHelper;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class Builder
{
    private $factory;
    private $tokenStorage;
    private $em;

    /**
     * Builder constructor.
     * @param FactoryInterface $factory
     * @param TokenStorageInterface $tokenStorage
     * @param EntityManagerInterface $em
     */
    public function __construct(FactoryInterface $factory, TokenStorageInterface $tokenStorage, EntityManagerInterface $em)
    {
        $this->factory = $factory;
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
    }

    /**
     * @param array $options
     * @return ItemInterface
     */
    public function mainMenu(array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $menu = $this->factory->createItem('root', [
            'childrenAttributes'=> [
                'class'=>'kt-menu__nav'
            ]
        ]);

        $menu->addChild(
            'Dashboard',
            [
                'route' => 'dashboard',
                'attributes' => [
                    'class' => (isset($options['active_menu1']) && $options['active_menu1'] == 'dashboard') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                    'aria-haspopup' => true,
                ],
                'linkAttributes' => [
                    'class' => 'kt-menu__link '
                ],
            ]
        );
        $siteAndLeaseRoute = '';
        if(UserHelper::checkPermission($this->em, $user, 'Lease', 'viewable')) {
            $siteAndLeaseRoute = 'lease_list';
        }
        elseif (UserHelper::checkPermission($this->em, $user, 'Site', 'viewable')){
            $siteAndLeaseRoute = 'site_list';
        }
        elseif (UserHelper::checkPermission($this->em, $user, 'Landlord', 'viewable')){
            $siteAndLeaseRoute = 'landlord_list';
        }
        elseif (UserHelper::checkPermission($this->em, $user, 'Financial', 'viewable')){
            $siteAndLeaseRoute = 'financial_list';
        }
        elseif (UserHelper::checkPermission($this->em, $user, 'Issue', 'viewable')){
            $siteAndLeaseRoute = 'issue_list';
        }
        if(!empty($siteAndLeaseRoute)){
            $menu->addChild('Sites and Leases', [
                'route' => $siteAndLeaseRoute,
                'attributes'=>[
                    'class'=>(isset($options['active_menu1']) && $options['active_menu1'] == 'sites_leases') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                    'aria-haspopup'=>true,
                ],
                'linkAttributes'=>[
                    'class'=>'kt-menu__link '
                ],
            ]);
        }

        if(UserHelper::checkPermission($this->em, $user, 'Report', 'viewable')) {
            $menu->addChild(
                'Reports',
                [
                    'route' => 'reports_list',
                    'attributes' => [
                        'class' => (isset($options['active_menu1']) && $options['active_menu1'] == 'reports') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        $agentRoute = '';
        if(UserHelper::checkPermission($this->em, $user, 'Agent', 'viewable')) {
            $agentRoute = 'agent_dashboard';
        }
        elseif (UserHelper::checkPermission($this->em, $user, 'Agent Saving', 'viewable')){
            $agentRoute = 'agent_saving';
        }
        elseif (UserHelper::checkPermission($this->em, $user, 'Agent Billing', 'viewable')){
            $agentRoute = 'agent_billing';
        }
        elseif (UserHelper::checkPermission($this->em, $user, 'Agent User', 'viewable')){
            $agentRoute = 'agent_user';
        }
        if(!empty($agentRoute)){
            $menu->addChild(
                'Agent',
                [
                    'route' => $agentRoute,
                    'attributes' => [
                        'class' => (isset($options['active_menu1']) && $options['active_menu1'] == 'agents') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        $settingRoute = '';
        if(UserHelper::checkPermission($this->em, $user, 'Setting', 'viewable')) {
            $settingRoute = 'settings_list';
        }
        elseif ($user->hasRole('ROLE_SUPER_ADMIN')){
            $settingRoute = 'user_list';
        }
        elseif(UserHelper::checkPermission($this->em, $user, 'Reminder', 'viewable')){
            $settingRoute = 'reminders_list';
        }
        if(!empty($settingRoute)){
            $menu->addChild(
                'Settings',
                [
                    'route' => $settingRoute,
                    'attributes' => [
                        'class' => (isset($options['active_menu1']) && $options['active_menu1'] == 'settings') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }


        return $menu;
    }

    /**
     * @param array $options
     * @return ItemInterface
     */
    public function siteAndLeaseMenu(array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $menu = $this->factory->createItem('root', [
            'childrenAttributes'=> [
                'class'=>'kt-menu__nav'
            ]
        ]);

        if(UserHelper::checkPermission($this->em, $user, 'Lease', 'viewable')){
            $menu->addChild(
                'Leases',
                [
                    'route' => 'lease_list',
                    'attributes'=>[
                        'class'=>(isset($options['active_menu2']) && $options['active_menu2'] == 'leases') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup'=>true,
                        'icon'=>(isset($options['active_menu2']) && $options['active_menu2'] == 'leases') ? 'fa fa-building text-blue' : 'fa fa-building'
                    ],
                    'linkAttributes'=>[
                        'class'=>'kt-menu__link '
                    ],
                ]
            );
        }

        if(UserHelper::checkPermission($this->em, $user, 'Site', 'viewable')) {
            $menu->addChild(
                'Sites',
                [
                    'route' => 'site_list',
                    'attributes' => [
                        'class' => (isset($options['active_menu2']) && $options['active_menu2'] == 'sites') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                        'icon' => (isset($options['active_menu2']) && $options['active_menu2'] == 'sites') ? 'fa fa-map-marked-alt text-blue' : 'fa fa-map-marked-alt'
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        if(UserHelper::checkPermission($this->em, $user, 'Landlord', 'viewable')) {
            $menu->addChild(
                'Landlords',
                [
                    'route' => 'landlord_list',
                    'attributes' => [
                        'class' => (isset($options['active_menu2']) && $options['active_menu2'] == 'landlords') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                        'icon' => (isset($options['active_menu2']) && $options['active_menu2'] == 'landlords') ? 'fa fa-users text-blue' : 'fa fa-users'
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        if(UserHelper::checkPermission($this->em, $user, 'Financial', 'viewable')) {
            $menu->addChild(
                'Financial',
                [
                    'route' => 'financial_list',
                    'attributes' => [
                        'class' => (isset($options['active_menu2']) && $options['active_menu2'] == 'financial') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                        'icon' => (isset($options['active_menu2']) && $options['active_menu2'] == 'financial') ? 'fa fa-coins text-blue' : 'fa fa-coins'
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        if(UserHelper::checkPermission($this->em, $user, 'Issue', 'viewable')) {
            $menu->addChild(
                'Issue',
                [
                    'route' => 'issue_list',
                    'attributes' => [
                        'class' => (isset($options['active_menu2']) && $options['active_menu2'] == 'issue') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                        'icon' => (isset($options['active_menu2']) && $options['active_menu2'] == 'issue') ? 'fa fa-exclamation-circle text-blue' : 'fa fa-exclamation-circle'
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        return $menu;
    }

    /**
     * @param array $options
     * @return ItemInterface
     */
    public function agentMenu(array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $menu = $this->factory->createItem('root', [
            'childrenAttributes'=> [
                'class'=>'kt-menu__nav'
            ]
        ]);

        if(UserHelper::checkPermission($this->em, $user, 'Agent', 'viewable')) {
            $menu->addChild(
                'Agent Dashboard',
                [
                    'route' => 'agent_dashboard',
                    'attributes' => [
                        'class' => (isset($options['active_menu2']) && $options['active_menu2'] == 'agent_dashboard') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                        'icon' => (isset($options['active_menu2']) && $options['active_menu2'] == 'agent_dashboard') ? 'la la-dashboard text-blue' : 'la la-dashboard'
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        if(UserHelper::checkPermission($this->em, $user, 'Agent Saving', 'viewable')) {
            $menu->addChild(
                'Agent Savings',
                [
                    'route' => 'agent_saving',
                    'attributes' => [
                        'class' => (isset($options['active_menu2']) && $options['active_menu2'] == 'agent_saving') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                        'icon' => (isset($options['active_menu2']) && $options['active_menu2'] == 'agent_saving') ? 'fa fa-save text-blue' : 'fa fa-save'
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        if(UserHelper::checkPermission($this->em, $user, 'Agent Billing', 'viewable')) {
            $menu->addChild(
                'Agent Billing',
                [
                    'route' => 'agent_billing',
                    'attributes' => [
                        'class' => (isset($options['active_menu2']) && $options['active_menu2'] == 'agent_billing') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                        'icon' => (isset($options['active_menu2']) && $options['active_menu2'] == 'agent_billing') ? 'fa fa-money-bill text-blue' : 'fa fa-money-bill'
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        if(UserHelper::checkPermission($this->em, $user, 'Agent User', 'viewable')) {
            $menu->addChild(
                'Agent Users',
                [
                    'route' => 'agent_user',
                    'attributes' => [
                        'class' => (isset($options['active_menu2']) && $options['active_menu2'] == 'agent_user') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                        'icon' => (isset($options['active_menu2']) && $options['active_menu2'] == 'agent_user') ? 'fa fa-users text-blue' : 'fa fa-users'
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        return $menu;
    }

    /**
     * @param array $options
     * @return ItemInterface
     */
    public function settingsMenu(array $options)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $menu = $this->factory->createItem('root', [
            'childrenAttributes'=> [
                'class'=>'kt-menu__nav'
            ]
        ]);

        if(UserHelper::checkPermission($this->em, $user, 'Setting', 'viewable')){
            $menu->addChild(
                'General',
                [
                    'route' => 'settings_list',
                    'attributes'=>[
                        'class'=>(isset($options['active_menu2']) && $options['active_menu2'] == 'general') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup'=>true,
                        'icon'=>(isset($options['active_menu2']) && $options['active_menu2'] == 'general') ? 'fa fa-cogs text-blue' : 'fa fa-cogs'
                    ],
                    'linkAttributes'=>[
                        'class'=>'kt-menu__link '
                    ],
                ]
            );

            $menu->addChild(
                'CPI Rates',
                [
                    'route' => 'settings_cpi_rates',
                    'attributes'=>[
                        'class'=>(isset($options['active_menu2']) && $options['active_menu2'] == 'cpi_rates') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup'=>true,
                        'icon'=>(isset($options['active_menu2']) && $options['active_menu2'] == 'cpi_rates') ? 'fa fa-percent text-blue' : 'fa fa-percent'
                    ],
                    'linkAttributes'=>[
                        'class'=>'kt-menu__link '
                    ],
                ]
            );

        }

        if($user->hasRole('ROLE_SUPER_ADMIN')) {
            $menu->addChild(
                'Users',
                [
                    'route' => 'user_list',
                    'attributes'=>[
                        'class'=>(isset($options['active_menu2']) && $options['active_menu2'] == 'users') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup'=>true,
                        'icon'=>(isset($options['active_menu2']) && $options['active_menu2'] == 'users') ? 'fa fa-users text-blue' : 'fa fa-users'
                    ],
                    'linkAttributes'=>[
                        'class'=>'kt-menu__link '
                    ],
                ]
            );
        }

        if(UserHelper::checkPermission($this->em, $user, 'Reminder', 'viewable')) {
            $menu->addChild(
                'Reminders',
                [
                    'route' => 'reminders_list',
                    'attributes' => [
                        'class' => (isset($options['active_menu2']) && $options['active_menu2'] == 'reminders') ? 'kt-menu__item kt-menu__item--here' : 'kt-menu__item',
                        'aria-haspopup' => true,
                        'icon'=>(isset($options['active_menu2']) && $options['active_menu2'] == 'reminders') ? 'fa fa-bell text-blue' : 'fa fa-bell'
                    ],
                    'linkAttributes' => [
                        'class' => 'kt-menu__link '
                    ],
                ]
            );
        }

        return $menu;
    }
}
