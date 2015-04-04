<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of existUser
 *
 * @author uživatel
 */

namespace tests\testBundle\isSession;

use tests\testBundle\Controller;
use Symfony\Component\HttpFoundation\Session\Session;
use Doctrine\ORM\EntityRepository;

class exist extends Controller\DefaultController {

    public function user(Session $session, EntityRepository $repositoryU) {
        if ($session->has('login')) {               // pokud session existuje
            $login = $session->get('login');        // vytahovani dat ze session
            $username = $login->getUsername();      // get Username
            $password = $login->getPassword();      // get Password
            $user = $repositoryU->findOneBy(array('username' => $username, 'password' => $password));     // vyhledani shody v DB
            return $user;
        } else { }
    }
    
    public function admin(Session $session, EntityRepository $repositoryA) {
        if ($session->has('aLogin')) {               // pokud session existuje
            $aLogin = $session->get('aLogin');       // vytahovani dat ze session
            $admin = $aLogin->getUsername();         // get Username
            $password = $aLogin->getPassword();      // get Password
            $userA = $repositoryA->findOneBy(array('admin' => $admin, 'password' => $password));
            return $userA;
        } else { }
    }
    
    public function test(Session $session, EntityRepository $repositoryT) {
        if ($session->has('fillTest')) {               // pokud session existuje
            $test = $session->get('fillTest');       // vytahovani dat ze session
            $code = $test->getCode();         // get Username
            $isTest = $repositoryT->findOneBy(array('code' => $code));
            return $isTest;
        } else { }
    }

}
