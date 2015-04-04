<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminController
 *
 * @author Štefan Prokop
 */

namespace tests\testBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use tests\testBundle\Modals\Admin;
use tests\testBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use tests\testBundle\isSession;

class AdminController extends Controller {

    public function adminAction(Request $request) {
// login form - Admin login
        $session = $this->getRequest()->getSession();                   // vytvoreni session
        $em = $this->getDoctrine()->getManager();                 // vytvoreni entity manager
        $repositoryA = $em->getRepository('teststestBundle:Admin');     // tvorba repozitare - tabulka Admin
        if ($request->getMethod() == 'POST') {                          // pokud byl odeslan formular
            $session->clear();                                          // vycisteni session
            $username = $request->get('username');                      // promenna Username - z formulare
            $password = sha1($request->get('password'));                      // promenna Password - z formulare

            $session = $this->getRequest()->getSession();
            $admin = $repositoryA->findOneBy(array('admin' => $username, 'password' => $password));     // vyhledani dat v DB
            if ($admin) {                               // pokud se data shoduji s nalezenym
                $aLogin = new Admin();                   // vytvoreni objektu login - slouzi pro ulozeni session
                $aLogin->setUsername($username);         // set Username
                $aLogin->setPassword($password);         // set Password
                $session->set('aLogin', $aLogin);         // ulozeni dat do session
                return $this->render('teststestBundle:Admin:adminProfile.html.twig', array('name' => ucfirst($admin->getName()), 'surname' => ucfirst($admin->getSurname()), 'school' => $admin->getSchool()));         // renderuje profil admina
            } else {        // pokud shoda neexistuje
                return $this->render('teststestBundle:Admin:admin.html.twig', array('error' => 'Uživatelské jméno nebo heslo je nesprávné'));         // vypisuje chybu na aktualni strance
            }
        } else {                                         // form nebyl odeslan
            $a = new isSession\exist;
            $admin = $a->admin($session, $repositoryA);
            if ($admin) {       // shoda existuje
                return $this->render('teststestBundle:Admin:adminProfile.html.twig', array('username' => $admin, 'school' => $admin->getSchool()));     // renderuje profil
            }
            return $this->render('teststestBundle:Admin:admin.html.twig');        // renderuje zakladni admin registracni stranu
        }
    }

    public function registerAction(Request $request) {
        // register user
        $session = $this->getRequest()->getSession();                   // vytvoreni session
        $em = $this->getDoctrine()->getManager();                 // vytvoreni entity manager
        $repositoryA = $em->getRepository('teststestBundle:Admin');      // tvorba repozitare - tabulka User

        $a = new isSession\exist;
        $admin = $a->admin($session, $repositoryA);
        if ($admin) {
            if ($request->getMethod() == 'POST') {          // formular byl odeslan
                $name = trim($request->get('name'));              // informace z formulare - jmeno, prijmeni, username, heslo, email a ze skryteho pole nactena promenna school
                $surname = trim($request->get('surname'));
                $username_reg = trim($request->get('username'));
                $email = trim($request->get('email'));
                $password_reg = $request->get('password');
                $admin_school = trim($request->get('school'));

                $repositoryU = $em->getRepository('teststestBundle:User');                       // repozitar pro tabulku User
                $sameUser = $repositoryU->findOneBy(array('username' => $username_reg));        // overeni zda zadane uzivatelske jmeno jiz neexistuje
                if (strtolower($username_reg) === 'admin' || strtolower($username_reg) === 'administrator') {    // pokud je uz. jmeno admin nebo administrator
                    return $this->render('teststestBundle:Admin:adminProfile.html.twig', array('username' => $admin, 'school' => $admin->getSchool(), 'error' => "Uživatelské jméno nemůže obsahovat slova admin nebo administrator."));   // vypise se chybove hlaseni
                } else if ($sameUser == NULL) {                        // pokud ne
                    $user = new User();                         // vytvori tridu User

                    $user->setName($name);                      // ukladani promennych do DB
                    $user->setSurname($surname);
                    $user->setUsername($username_reg);
                    $user->setEmail($email);
                    $user->setPassword(sha1($password_reg));
                    $user->setSchool($admin_school);

                    $em->persist($user);                                // zpracovani dat
                    $em->flush();                                       // zapis dat do DB

                    $subject = "Registrační údaje";                                // odeslani registracnich udaju novemu uzivateli
                    $headers = "FROM: testy@online.cz" . "\r\n";
                    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
                    $body = $this->renderView("teststestBundle:Admin:registerEmailLayout.html.twig", array('name' => $name, 'surname' => $surname, 'username' => $username_reg, 'email' => $email, 'password' => $password_reg, 'school' => $admin_school));
                    mail($email, $subject, $body, $headers);

                    return $this->render('teststestBundle:Admin:adminProfile.html.twig', array('username' => $admin, 'school' => $admin->getSchool(), 'message' => "Uživatel $username_reg byl úspěšně registrován.")); // pokud byl uzivatel registrovan vypise se hlaseni
                } else {        // pokud uzivatelske jmeno existuje
                    return $this->render('teststestBundle:Admin:adminProfile.html.twig', array('username' => $admin, 'school' => $admin->getSchool(), 'error' => "Uživatel $username_reg již existuje, zadejte prosím jiné uživatelské jméno."));   // vypise se chybove hlaseni
                }
            } else {        // pokud nebyl odeslan formular
                if ($admin) {       // pokud admin existuje
                    return $this->render('teststestBundle:Admin:adminProfile.html.twig', array('username' => $admin, 'school' => $admin->getSchool()));     // renderuje profil
                }
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');        // renderuje prihlasovaci stranku
    }

    public function dataAction() {
        // nahled dat - Admin
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryA = $em->getRepository('teststestBundle:Admin');
        $repositoryU = $em->getRepository('teststestBundle:User');
        $session = $this->getRequest()->getSession();

        $a = new isSession\exist;
        $admin = $a->admin($session, $repositoryA);
        if ($admin) {
            $selectUser = $repositoryU->findBy(array('school' => $admin->getSchool()));
            return $this->render('teststestBundle:Admin:data.html.twig', array('username' => $admin, 'users' => $selectUser));
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

    public function dataUserAction() {
        // data uzivatelu 
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryA = $em->getRepository('teststestBundle:Admin');
        $repositoryAn = $em->getRepository('teststestBundle:Answer');
        $repositoryQ = $em->getRepository('teststestBundle:Question');
        $repositoryU = $em->getRepository('teststestBundle:User');
        $session = $this->getRequest()->getSession();

        $a = new isSession\exist;
        $admin = $a->admin($session, $repositoryA);
        if ($admin) {
            $id = $_COOKIE['id'];       // ulozeni test id do COOKIE
            $selectUser = $repositoryU->findBy(array('id' => $id));     // selektace user, testu, otazek a odpovei
            $selectTest = $em->createQueryBuilder()
                    ->select('t')
                    ->from('teststestBundle:Test', 't')
                    ->where('t.userId = :user_id')
                    ->setParameter('user_id', $id)
                    ->getQuery()
                    ->getArrayResult();
            $selectQuestion = $repositoryQ->findAll();
            $selectAnswer = $repositoryAn->findAll();
            return $this->render('teststestBundle:Admin:dataUser.html.twig', array('username' => $admin, 'user' => $selectUser, 'tests' => $selectTest, 'questions' => $selectQuestion, 'answers' => $selectAnswer));
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

    public function deleteUserAction(Request $request) {
        // odstraneni uzivatele
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $session = $this->getRequest()->getSession();
        $repositoryA = $em->getRepository('teststestBundle:Admin');
        $repositoryU = $em->getRepository('teststestBundle:User');
        $repositoryAn = $em->getRepository('teststestBundle:Answer');
        $repositoryQ = $em->getRepository('teststestBundle:Question');

        $a = new isSession\exist;
        $admin = $a->admin($session, $repositoryA);

        if ($admin) {
            if ($request->getMethod() == 'POST') {
                $id = $request->get("user_id");
                $name = $request->get("user_name");
                $surname = $request->get("user_surname");

                $selectTest = $em->createQueryBuilder()         // selektace testu
                        ->select('t')
                        ->from('teststestBundle:Test', 't')
                        ->where('t.userId = :user_id')
                        ->setParameter('user_id', $id)
                        ->getQuery()
                        ->getArrayResult();
                $removingU = $em->createQuery("DELETE FROM teststestBundle:User u WHERE u.id = " . $id);
                $removingU->execute();      // odstraneni uzivatele

                if ($selectTest) {      // pokud uzivatel vlastnil test
                    for ($i = 0; $i < count($selectTest); $i++) {       // odstraneni testu
                        $removingT = $em->createQuery("DELETE FROM teststestBundle:Test t WHERE t.userId = " . $id);
                        $removingT->execute();

                        $selectQid = $em->createQueryBuilder()          // selektace otazek
                                ->select('partial q.{id}')
                                ->from('teststestBundle:Question', 'q')
                                ->where('q.testId = :test_id')
                                ->setParameter('test_id', $selectTest[$i]['id'])
                                ->getQuery()
                                ->getArrayResult();

                        for ($j = 0; $j < count($selectQid); $j++) {        // podle id otazek odstrani odpovedi
                            $removingA = $em->createQuery("DELETE FROM teststestBundle:Answer a WHERE a.questionId = " . $selectQid[$j]['id']);
                            $removingA->execute();
                        }

                        // nakonec odstranime otazky
                        $removingQ = $em->createQuery("DELETE FROM teststestBundle:Question q WHERE q.testId = " . $selectTest[$i]['id']);
                        $removingQ->execute();
                    }
                }

                return $this->render('teststestBundle:Admin:deleteUser.html.twig', array('name' => $name, 'surname' => $surname, 'username' => $admin));
            } else {
                if ($admin) {
                    // obycejny vypis uzivatele
                    $id = $_COOKIE['id'];
                    $selectUser = $repositoryU->findBy(array('id' => $id));
                    $selectTest = $em->createQueryBuilder()
                            ->select('t')
                            ->from('teststestBundle:Test', 't')
                            ->where('t.userId = :user_id')
                            ->setParameter('user_id', $id)
                            ->getQuery()
                            ->getArrayResult();
                    $selectQuestion = $repositoryQ->findAll();
                    $selectAnswer = $repositoryAn->findAll();
                    if (!$selectUser) {
                        return $this->render('teststestBundle:Admin:adminProfile.html.twig', array('username' => $admin, 'school' => $admin->getSchool()));
                    }
                    return $this->render('teststestBundle:Admin:dataUser.html.twig', array('username' => $admin, 'user' => $selectUser, 'tests' => $selectTest, 'questions' => $selectQuestion, 'answers' => $selectAnswer));
                }
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

    public function changePasswordAction(Request $request) {
        // zmena hesla uzivatele - Admin
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryA = $em->getRepository('teststestBundle:Admin');
        $session = $this->getRequest()->getSession();

        $a = new isSession\exist;
        $admin = $a->admin($session, $repositoryA);
        if ($admin) {           // pouziva se v pripade, ze uzivatel zapomnel heslo
            if ($request->getMethod() == 'POST') {
                $user_id = $request->get('user_id');
                $new_password = sha1($request->get('new-psw'));
                $for_email = $request->get('new-psw');
                $selectEmail = $em->createQueryBuilder()        // nalezne email kvuli zprave
                        ->select('u')
                        ->from('teststestBundle:User', 'u')
                        ->where('u.id = :id')
                        ->setParameter('id', $user_id)
                        ->getQuery()
                        ->getArrayResult();
                $updatePsw = $em->createQueryBuilder()          // update hesla v tabulce
                        ->update('teststestBundle:User', 'u')
                        ->set('u.password', '?1')
                        ->where('u.id = :id')
                        ->setParameter('1', $new_password)
                        ->setParameter('id', $user_id)
                        ->getQuery();
                if ($updatePsw->execute()) {        // pokud update probehne uspesne
                    $subject = "Změna hesla";       // odesle se email
                    $headers = "FROM: testy@online.cz" . "\r\n";
                    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
                    $email = $selectEmail[0]['email'];
                    $body = $this->renderView("teststestBundle:Admin:changePswEmailLayout.html.twig", array('new_psw' => $for_email));
                    mail($email, $subject, $body, $headers);
                    return $this->render('teststestBundle:Admin:adminProfile.html.twig', array('username' => $admin, 'school' => $admin->getSchool(), 'message' => 'Uživatelské heslo bylo úspěšně změněno'));
                } else {
                    return $this->render('teststestBundle:Admin:adminProfile.html.twig', array('username' => $admin, 'school' => $admin->getSchool(), 'error' => 'Uživatelské heslo nebylo změněno'));
                }
            } else {
                return $this->render('teststestBundle:Admin:adminProfile.html.twig', array('username' => $admin, 'school' => $admin->getSchool()));
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

}
