<?php


namespace App\Helper;


use App\Entity\Permission;
use App\Entity\UserPermission;
use Doctrine\ORM\EntityManager;

/**
 * Class UserHelper
 * @package App\Helper
 */
class UserHelper
{

    /**
     * @param EntityManager $em
     * @param $user
     * @param $name
     * @param $action
     * @return bool
     */
    public static function checkPermission(EntityManager $em, $user, $name, $action){
        $permission = $em->getRepository(Permission::class)->findOneBy(['name'=>$name]);
        if($permission instanceof Permission){
            $userPermission = $em->getRepository(UserPermission::class)->findOneBy(['permission'=>$permission, 'user'=>$user, "$action"=>true]);
            if($userPermission instanceof UserPermission){
                return true;
            }
        }

        return false;
    }

    /**
     * @param EntityManager $em
     * @param $user
     * @param $name
     * @return array
     */
    public static function getUserPermissionByName(EntityManager $em, $user, $name){
        $permissions = [];
        $permission = $em->getRepository(Permission::class)->findOneBy(['name'=>$name]);
        if($permission instanceof Permission){
            $userPermission = $em->getRepository(UserPermission::class)->findOneBy(['permission'=>$permission, 'user'=>$user]);
            if($userPermission instanceof UserPermission){
                $permissions = [
                    'view' => $userPermission->getViewable(),
                    'add' => $userPermission->getAdded(),
                    'edit' => $userPermission->getEditable(),
                    'remove' => $userPermission->getRemovable()
                ];
            }
        }

        return $permissions;
    }

}
