<?php

namespace tests\testBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use tests\testBundle\Entity\Test;
use tests\testBundle\Modals\Login;
use tests\testBundle\Modals\newTest;
use tests\testBundle\Entity\Grade;
use tests\testBundle\isSession;
use tests\testBundle\questionAnswer;

class DefaultController extends Controller {

    public function indexAction(Request $request) {
// login form - User login
        $session = $this->getRequest()->getSession();                   // vytvoreni session
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryU = $em->getRepository('teststestBundle:User');      // otevreni repositare - tabulka User
        if ($session->has('newTest')) {
            $newTest = $session->get('newTest');
            $newTest->setId("");
        }
        if ($request->getMethod() == 'POST') {                          // pokud byl odeslan form
            $session->clear();                                          // vynulovani session
            $username = $request->get('username');                      // promenna username - z formulare
            $password = sha1($request->get('password'));                      // promenna password - z formulare

            $user = $repositoryU->findOneBy(array('username' => $username, 'password' => $password));   // vyhledani v DB shody username a password
            if ($user) {                                // pokud existuje shoda
                $login = new Login();                   // vytvoreni noveho objektu Login
                $login->setUsername($username);         // set Username - pro session
                $login->setPassword($password);         // set Password - pro session
                $session->set('login', $login);         // ulozeni prihlasovacich udaju do session
                $session->set('test', "");
                $userId = $user->getId();
                $tests = $em->createQueryBuilder()
                        ->select('t')
                        ->from('teststestBundle:Test', 't')
                        ->where('t.userId = :userId')
                        ->setParameter('userId', $userId)
                        ->getQuery()
                        ->getResult();
                return $this->render('teststestBundle:Default:profile.html.twig', array('name' => ucfirst($user->getName()), 'surname' => ucfirst($user->getSurname()), 'tests' => $tests));     // otevreni profilu
            } else {        // pokud se neshoduje
                return $this->render('teststestBundle:Default:home.html.twig', array('error' => 'Uživatelské jméno nebo heslo je nesprávné'));              // zustava na stejne strance + zobrazi error
            }
        } else {
            $u = new isSession\exist;
            $user = $u->user($session, $repositoryU);
            if ($user) {       // pokud shoda existuje
                $session->set('test', "");         // vynulovani session
                $userId = $user->getId();
                $tests = $em->createQueryBuilder()
                        ->select('t')
                        ->from('teststestBundle:Test', 't')
                        ->where('t.userId = :userId')
                        ->setParameter('userId', $userId)
                        ->getQuery()
                        ->getResult();
                return $this->render('teststestBundle:Default:profile.html.twig', array('username' => $user, 'tests' => $tests));     // renderuje profil uzivatele
            }
            return $this->render('teststestBundle:Default:home.html.twig');         // renderuje zakladni stranku home.html.twig
        }
    }

    public function logoutAction() {
// logout action
        $session = $this->getRequest()->getSession();       // vytvoreni session / nacteni
        $session->clear();                                  // vymazani obsahu session
        return $this->render('teststestBundle:Default:home.html.twig'); // renderovani uvodni strany - home.html.twig
    }

    public function newAction() {
        // Create new test page
        $session = $this->getRequest()->getSession();       // vytvoreni session / nacteni
        $em = $this->getDoctrine()->getManager();                 // vytvoreni entity manager
        $repositoryU = $em->getRepository('teststestBundle:User');      // tvorba repozitare - tabulka User

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {       // shoda existuje
            return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user));     // renderuje create page
        }
        return $this->render('teststestBundle:Default:home.html.twig');        // renderuje prihlasovaci stranku
    }

    public function newSaveAction(Request $request) {
        // save new test
        $session = $this->getRequest()->getSession();                   // vytvoreni session
        $em = $this->getDoctrine()->getManager();                 // vytvoreni entity manager
        $repositoryU = $em->getRepository('teststestBundle:User');      // tvorba repozitare - tabulka User

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            if ($request->getMethod() == 'POST') {          // formular byl odeslan
                setlocale(LC_ALL, 'czech');
                $testName = trim($request->get('testName'));              // informace z formulare - jmeno, prijmeni, username, heslo, email a ze skryteho pole nactena promenna school
                $testEmail = trim($request->get('testEmail'));
                $testDescription = trim($request->get('testDescription'));
                $testTime = trim($request->get('testTime'));
                $testClass = trim($request->get('testClass'));
                $code = trim($request->get('testCode'));            // tabulka pro trans. kodu (aby byl bez diakritiky)
                $table = array(
                    'ä' => 'a',
                    'Ä' => 'A',
                    'á' => 'a',
                    'Á' => 'A',
                    'à' => 'a',
                    'À' => 'A',
                    'ã' => 'a',
                    'Ã' => 'A',
                    'â' => 'a',
                    'Â' => 'A',
                    'č' => 'c',
                    'Č' => 'C',
                    'ć' => 'c',
                    'Ć' => 'C',
                    'ď' => 'd',
                    'Ď' => 'D',
                    'ě' => 'e',
                    'Ě' => 'E',
                    'é' => 'e',
                    'É' => 'E',
                    'ë' => 'e',
                    'Ë' => 'E',
                    'è' => 'e',
                    'È' => 'E',
                    'ê' => 'e',
                    'Ê' => 'E',
                    'í' => 'i',
                    'Í' => 'I',
                    'ï' => 'i',
                    'Ï' => 'I',
                    'ì' => 'i',
                    'Ì' => 'I',
                    'î' => 'i',
                    'Î' => 'I',
                    'ľ' => 'l',
                    'Ľ' => 'L',
                    'ĺ' => 'l',
                    'Ĺ' => 'L',
                    'ń' => 'n',
                    'Ń' => 'N',
                    'ň' => 'n',
                    'Ň' => 'N',
                    'ñ' => 'n',
                    'Ñ' => 'N',
                    'ó' => 'o',
                    'Ó' => 'O',
                    'ö' => 'o',
                    'Ö' => 'O',
                    'ô' => 'o',
                    'Ô' => 'O',
                    'ò' => 'o',
                    'Ò' => 'O',
                    'õ' => 'o',
                    'Õ' => 'O',
                    'ő' => 'o',
                    'Ő' => 'O',
                    'ř' => 'r',
                    'Ř' => 'R',
                    'ŕ' => 'r',
                    'Ŕ' => 'R',
                    'š' => 's',
                    'Š' => 'S',
                    'ś' => 's',
                    'Ś' => 'S',
                    'ť' => 't',
                    'Ť' => 'T',
                    'ú' => 'u',
                    'Ú' => 'U',
                    'ů' => 'u',
                    'Ů' => 'U',
                    'ü' => 'u',
                    'Ü' => 'U',
                    'ù' => 'u',
                    'Ù' => 'U',
                    'ũ' => 'u',
                    'Ũ' => 'U',
                    'û' => 'u',
                    'Û' => 'U',
                    'ý' => 'y',
                    'Ý' => 'Y',
                    'ž' => 'z',
                    'Ž' => 'Z',
                    'ź' => 'z',
                    'Ź' => 'Z'
                );
                $testCode = strtr($code, $table);       // trans. kodu

                $testUserId = $user->getId();
                $testTeacher = $user->getName() . " " . $user->getSurname();

                $repositoryT = $em->getRepository('teststestBundle:Test');                       // repozitar pro tabulku User
                $sameTest = $repositoryT->findOneBy(array('name' => $testName, 'userId' => $testUserId));        // overeni zda zadane uzivatelske jmeno jiz neexistuje
                if ($sameTest === NULL) {                        // pokud ne
                    $test = new Test();                         // vytvori tridu User

                    $test->setName($testName);                      // ukladani promennych do DB
                    $test->setEmail($testEmail);
                    $test->setDescription($testDescription);
                    $test->setTime($testTime);
                    $test->setCode($testCode);
                    $test->setClass($testClass);
                    $test->setUserId($testUserId);
                    $test->setTeacher($testTeacher);

                    $em->persist($test);                                // zpracovani dat
                    $em->flush();                                       // zapis dat do DB test

                    $html = "<h1>$testName</h1><h3>Popis testu: $testDescription</h3>"    // ulozeni do session pro pozdejsi cteni (zobrazeni nahledu testu)
                            . "<span>Email pro zaslání výsledků: $testEmail<br />"
                            . "Časové omezení testu: $testTime<br />Test pro třídu: $testClass<br />"
                            . "Kód testu: $testCode</span>";
                    $session->set("test", $html);         // vytvoreni session test

                    $testID = $test->getId();
                    $newTest = new newTest();                   // vytvoreni objektu login - slouzi pro ulozeni session
                    $newTest->setId($testID);                   // set Username
                    $session->set('newTest', $newTest);         // ulozeni dat do session
                    $testId = $newTest->getId();

                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user, 'message' => "Test $testName byl úspěšně vytvořen.", 'testId' => $testId, 'session' => $session->get('test'))); // pokud byl uzivatel registrovan vypise se hlaseni
                } else {
                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user, 'error' => "Test $testName již existuje, zadejte prosím jiné jméno testu."));   // vypise se chybove hlaseni
                }
            } else {        // pokud nebyl odeslan formular
                if ($session->has('newTest')) {
                    $newTest = $session->get('newTest');
                    $testId = $newTest->getId();

                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user, 'testId' => $testId));     // renderuje profil
                } else {
                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user));     // renderuje profil
                }
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');        // renderuje prihlasovaci stranku
    }

    public function newQuestionAction(Request $request) {
        // save new question
        $session = $this->getRequest()->getSession();                   // vytvoreni session
        $em = $this->getDoctrine()->getManager();                 // vytvoreni entity manager
        $repositoryU = $em->getRepository('teststestBundle:User');      // tvorba repozitare - tabulka User

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            if ($request->getMethod() == 'POST') {          // formular byl odeslan
                if ($session->has('newTest')) {
                    $questionText = trim($request->get('questionText'));
                    $tAnswer = trim($request->get('tAnswer'));
                    $answers = array(trim(strtolower($request->get('answer1'))), trim(strtolower($request->get('answer2'))), trim(strtolower($request->get('answer3'))));
                    $scale = $request->get('scale');
                    $newTest = $session->get('newTest');
                    $testId = $newTest->getId();

                    $nq = new questionAnswer\newQuestionAnswer();
                    $nq->normalQuestion($em, $scale, $testId, $questionText, $tAnswer, $answers);

                    $oldHtml = $session->get('test');       // nacteni stare session
                    $html = "<hr /><h3>$questionText</h3><ol><li>$answers[0]</li><li>$answers[1]</li>"
                            . "<li>$answers[2]</li></ol>";
                    $session->set("test", $oldHtml . $html);         // vytvoreni session

                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user, 'message' => "Otázka byla úspěšně vytvořena.", 'testId' => $testId, 'session' => $session->get('test'))); // pokud byl uzivatel registrovan vypise se hlaseni
                } else {
                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user, 'error' => "Otázku se nepovedlo vytvořit."));   // vypise se chybove hlaseni
                }
            } else {        // pokud nebyl odeslan formular
                if ($session->has('newTest')) {
                    $newTest = $session->get('newTest');
                    $testId = $newTest->getId();

                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user, 'testId' => $testId));     // renderuje profil
                } else {
                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user));     // renderuje profil
                }
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');        // renderuje prihlasovaci stranku
    }

    public function newQuestAnswAction(Request $request) {
        // new question answer save
        $session = $this->getRequest()->getSession();                   // vytvoreni session
        $em = $this->getDoctrine()->getManager();                 // vytvoreni entity manager
        $repositoryU = $em->getRepository('teststestBundle:User');      // tvorba repozitare - tabulka User

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            if ($request->getMethod() == 'POST') {          // formular byl odeslan
                if ($session->has('newTest')) {
                    $questionText = trim($request->get('questionText'));
                    $tAnswer = trim(strtolower($request->get('questionAnswer')));
                    $scale = $request->get('scale');

                    $newTest = $session->get('newTest');
                    $testId = $newTest->getId();

                    $na = new questionAnswer\newQuestionAnswer();
                    $na->questionAnswer($em, $testId, $scale, $questionText, $tAnswer);     // ulozeni do DB otazky a odpovedi

                    $oldHtml = $session->get('test');
                    $html = "<hr /><h3>$questionText</h3><ol><li>$tAnswer</li></ol>";    // stara session + pridani nove cookie
                    $session->set("test", $oldHtml . $html);         // vytvoreni session

                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user, 'message' => "Otázka byla úspěšně vytvořena.", 'testId' => $testId, 'session' => $session->get('test'))); // pokud byl uzivatel registrovan vypise se hlaseni
                } else {
                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user, 'error' => "Otázku se nepovedlo vytvořit."));   // vypise se chybove hlaseni
                }
            } else {        // pokud nebyl odeslan formular
                if ($session->has('newTest')) {
                    $newTest = $session->get('newTest');
                    $testId = $newTest->getId();

                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user, 'testId' => $testId));     // renderuje profil
                } else {      // pokud uzivatel existuje
                    return $this->render('teststestBundle:Default:createNewTest.html.twig', array('username' => $user));     // renderuje profil
                }
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');        // renderuje prihlasovaci stranku
    }

    public function removeTestAction(Request $request) {
        // removing test
        $session = $this->getRequest()->getSession();                   // vytvoreni session
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryU = $em->getRepository('teststestBundle:User');      // otevreni repositare - tabulka User
        $repositoryT = $em->getRepository('teststestBundle:Test');

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            if ($request->getMethod() == 'POST') {                          // pokud byl odeslan form
                $test_id = $request->get('test_id');

                $findT = $repositoryT->findOneBy(array('id' => $test_id, 'userId' => $user->getId()));
                if ($findT) {       // pokud je uzivatel vlastnikem testu
                    $removingT = $em->createQuery("DELETE FROM teststestBundle:Test t WHERE t.id = " . $test_id);
                    $removingT->execute();      // odstraneni testu

                    $selectQid = $em->createQueryBuilder()          // nalezne otazky
                            ->select('partial q.{id}')
                            ->from('teststestBundle:Question', 'q')
                            ->where('q.testId = :test_id')
                            ->setParameter('test_id', $test_id)
                            ->getQuery()
                            ->getArrayResult();

                    for ($i = 0; $i < count($selectQid); $i++) {        // smaze odpovedi podle ID otazek
                        $removingA = $em->createQuery("DELETE FROM teststestBundle:Answer a WHERE a.questionId = " . $selectQid[$i]['id']);
                        $removingA->execute();
                    }

                    // odstraneni otazek
                    $removingQ = $em->createQuery("DELETE FROM teststestBundle:Question q WHERE q.testId = " . $test_id);
                    $removingQ->execute();
                }           // vyhledani zbylych testu pro vypis na profilu
                $userId = $user->getId();
                $tests = $em->createQueryBuilder()
                        ->select('t')
                        ->from('teststestBundle:Test', 't')
                        ->where('t.userId = :userId')
                        ->setParameter('userId', $userId)
                        ->getQuery()
                        ->getResult();
                return $this->render('teststestBundle:Default:profile.html.twig', array('username' => $user, 'tests' => $tests));     // otevreni profilu
            } else {        // pokud shoda existuje
                $userId = $user->getId();
                $tests = $em->createQueryBuilder()
                        ->select('t')
                        ->from('teststestBundle:Test', 't')
                        ->where('t.userId = :userId')
                        ->setParameter('userId', $userId)
                        ->getQuery()
                        ->getResult();
                return $this->render('teststestBundle:Default:profile.html.twig', array('username' => $user, 'tests' => $tests));     // renderuje profil uzivatele
            }
            return $this->render('teststestBundle:Default:home.html.twig');         // renderuje zakladni stranku home.html.twig
        }
    }

    public function previewTestAction() {
        // nahled testu
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryU = $em->getRepository('teststestBundle:User');
        $repositoryT = $em->getRepository('teststestBundle:Test');
        $repositoryA = $em->getRepository('teststestBundle:Answer');
        $repositoryG = $em->getRepository('teststestBundle:Grade');

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            $authUT = $repositoryT->findOneBy(array('userId' => $user->getId(), 'id' => $_COOKIE['id']));
            if ($authUT) {      // pokud je uzivatel vlastnikem testu
                $id = $_COOKIE['id'];
                $selectTest = $repositoryT->findBy(array('id' => $id));
                $selectQuestion = $em->createQueryBuilder()
                        ->select('q')
                        ->from('teststestBundle:Question', 'q')
                        ->where('q.testId = :test_id')
                        ->setParameter('test_id', $id)
                        ->getQuery()
                        ->getArrayResult();
                $selectAnswer = $repositoryA->findAll();        // nalezeni testu, otazek a odpovedi
                $selectGrade = $repositoryG->findBy(array("testId" => $_COOKIE['id']));
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'tests' => $selectTest, 'questions' => $selectQuestion, 'answers' => $selectAnswer, 'grade' => $selectGrade));
            } else {
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'error' => 'K tomuto testu nemáte přístup'));
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

    public function settingAction(Request $request) {
        // nastaveni pro User and Admin
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryA = $em->getRepository('teststestBundle:Admin');
        $repositoryU = $em->getRepository('teststestBundle:User');

        $u = new isSession\exist;

        if ($u->admin($session, $repositoryA)) {
            // pokud je uzivatel admin predela heslo adminovi pokud je user tak userovi
            $admin = $u->admin($session, $repositoryA);
            $a = 1;
            if ($request->getMethod() == 'POST') {
                $oldPsw = sha1($request->get('oldPsw'));
                $newPsw = sha1($request->get('newPsw'));
                $psw = $repositoryA->findOneBy(array('admin' => $admin->getAdmin(), 'password' => $oldPsw));
                if ($psw) {
                    $updateA = $em->createQueryBuilder()
                            ->update('teststestBundle:Admin', 'a')
                            ->set('a.password', '?1')
                            ->where('a.id = :id')
                            ->setParameter('1', $newPsw)
                            ->setParameter('id', $admin->getId())
                            ->getQuery();
                    if ($updateA->execute()) {
                        return $this->render('teststestBundle:Default:setting.html.twig', array('username' => $admin, 'admin' => $a, 'message' => 'Vaše heslo bylo úspěšně změněno, pokračujte prosím tím, že se znovu přihlásíte'));
                    } else {
                        return $this->render('teststestBundle:Default:setting.html.twig', array('username' => $admin, 'admin' => $a, 'error' => 'Vaše heslo nebylo změněno'));
                    }
                } else {
                    return $this->render('teststestBundle:Default:setting.html.twig', array('username' => $admin, 'admin' => $a, 'error' => 'Staré zadané heslo se neshoduje s vaším pravým heslem'));
                }
            }
            return $this->render('teststestBundle:Default:setting.html.twig', array('username' => $admin, 'admin' => $a));
        } else if ($u->user($session, $repositoryU)) {
            $user = $u->user($session, $repositoryU);
            if ($request->getMethod() == 'POST') {
                $oldPsw = sha1($request->get('oldPsw'));
                $newPsw = sha1($request->get('newPsw'));
                $psw = $repositoryU->findOneBy(array('username' => $user->getUsername(), 'password' => $oldPsw));
                if ($psw) {
                    $updateU = $em->createQueryBuilder()
                            ->update('teststestBundle:User', 'u')
                            ->set('u.password', '?1')
                            ->where('u.id = :id')
                            ->setParameter('1', $newPsw)
                            ->setParameter('id', $user->getId())
                            ->getQuery();
                    if ($updateU->execute()) {
                        return $this->render('teststestBundle:Default:setting.html.twig', array('username' => $user, 'message' => 'Vaše heslo bylo úspěšně změněno, pokračujte prosím tím, že se znovu přihlásíte'));
                    } else {
                        return $this->render('teststestBundle:Default:setting.html.twig', array('username' => $user, 'error' => 'Vaše heslo nebylo změněno'));
                    }
                } else {
                    return $this->render('teststestBundle:Default:setting.html.twig', array('username' => $user, 'error' => 'Staré zadané heslo se neshoduje s vaším pravým heslem'));
                }
            }
            return $this->render('teststestBundle:Default:setting.html.twig', array('username' => $user));
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

    public function deleteOneQAction(Request $request) {
        // delete one question from preview
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryU = $em->getRepository('teststestBundle:User');
        $repositoryT = $em->getRepository('teststestBundle:Test');
        $repositoryA = $em->getRepository('teststestBundle:Answer');
        $repositoryQ = $em->getRepository('teststestBundle:Question');

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            if ($request->getMethod() == 'POST') {
                $questionId = $request->get('hidQid');
                $findQ = $repositoryQ->findOneBy(array('testId' => $_COOKIE['id'], 'id' => $questionId));
                if ($findQ) {     // pokud otazka nalezi testu tak ji smaze
                    $removingA = $em->createQuery("DELETE FROM teststestBundle:Answer a WHERE a.questionId = " . $questionId);
                    $removingA->execute();
                    $removingQ = $em->createQuery("DELETE FROM teststestBundle:Question q WHERE q.id = " . $questionId);
                    $removingQ->execute();
                }
            }

            $authUT = $repositoryT->findOneBy(array('userId' => $user->getId(), 'id' => $_COOKIE['id']));
            if ($authUT) {
                $id = $_COOKIE['id'];
                $selectTest = $repositoryT->findBy(array('id' => $id));
                $selectQuestion = $em->createQueryBuilder()
                        ->select('q')
                        ->from('teststestBundle:Question', 'q')
                        ->where('q.testId = :test_id')
                        ->setParameter('test_id', $id)
                        ->getQuery()
                        ->getArrayResult();
                $selectAnswer = $repositoryA->findAll();
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'tests' => $selectTest, 'questions' => $selectQuestion, 'answers' => $selectAnswer));
            } else {
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'error' => 'K tomuto testu nemáte přístup'));
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

    public function prevNewQuestionAction(Request $request) {
        // nahled testu
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryU = $em->getRepository('teststestBundle:User');
        $repositoryT = $em->getRepository('teststestBundle:Test');
        $repositoryA = $em->getRepository('teststestBundle:Answer');

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            if ($request->getMethod() == 'POST') {
                $questionText = trim($request->get('questionText'));
                $tAnswer = trim($request->get('tAnswer'));
                $answers = array(trim($request->get('answer1')), trim($request->get('answer2')), trim($request->get('answer3')));
                $scale = $request->get('scale');
                $testId = $request->get('testId');

                $findT = $repositoryT->findOneBy(array('userId' => $user->getId(), 'id' => $testId));
                // pokud nalezne shodny zaznam prida otazku
                if ($findT) {
                    $nq = new questionAnswer\newQuestionAnswer();
                    $nq->normalQuestion($em, $scale, $testId, $questionText, $tAnswer, $answers);
                }
            }

            $authUT = $repositoryT->findOneBy(array('userId' => $user->getId(), 'id' => $_COOKIE['id']));
            if ($authUT) {
                $id = $_COOKIE['id'];
                $selectTest = $repositoryT->findBy(array('id' => $id));
                $selectQuestion = $em->createQueryBuilder()
                        ->select('q')
                        ->from('teststestBundle:Question', 'q')
                        ->where('q.testId = :test_id')
                        ->setParameter('test_id', $id)
                        ->getQuery()
                        ->getArrayResult();
                $selectAnswer = $repositoryA->findAll();
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'tests' => $selectTest, 'questions' => $selectQuestion, 'answers' => $selectAnswer));
            } else {
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'error' => 'K tomuto testu nemáte přístup'));
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

    public function prevNewQuestionAnswerAction(Request $request) {
        // nahled testu
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryU = $em->getRepository('teststestBundle:User');
        $repositoryT = $em->getRepository('teststestBundle:Test');
        $repositoryA = $em->getRepository('teststestBundle:Answer');

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            if ($request->getMethod() == 'POST') {
                $questionText = trim($request->get('questionText'));
                $tAnswer = trim($request->get('questionAnswer'));
                $scale = $request->get('scale');
                $testId = $request->get('testId');

                $findT = $repositoryT->findOneBy(array('userId' => $user->getId(), 'id' => $testId));
                // pokud nalezne shodu prida otazku
                if ($findT) {
                    $na = new questionAnswer\newQuestionAnswer();
                    $na->questionAnswer($em, $testId, $scale, $questionText, $tAnswer);
                }
            }

            $authUT = $repositoryT->findOneBy(array('userId' => $user->getId(), 'id' => $_COOKIE['id']));
            if ($authUT) {
                $id = $_COOKIE['id'];
                $selectTest = $repositoryT->findBy(array('id' => $id));
                $selectQuestion = $em->createQueryBuilder()
                        ->select('q')
                        ->from('teststestBundle:Question', 'q')
                        ->where('q.testId = :test_id')
                        ->setParameter('test_id', $id)
                        ->getQuery()
                        ->getArrayResult();
                $selectAnswer = $repositoryA->findAll();
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'tests' => $selectTest, 'questions' => $selectQuestion, 'answers' => $selectAnswer));
            } else {
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'error' => 'K tomuto testu nemáte přístup'));
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

    public function editNewQuestionAnswerAction(Request $request) {
        // nahled testu
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryU = $em->getRepository('teststestBundle:User');
        $repositoryT = $em->getRepository('teststestBundle:Test');
        $repositoryA = $em->getRepository('teststestBundle:Answer');
        $repositoryQ = $em->getRepository('teststestBundle:Question');

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            if ($request->getMethod() == 'POST') {
                $questionId = $request->get('questionId');
                $questionText = trim($request->get('questionText'));
                $tAnswer = trim($request->get('questionAnswer'));
                $scale = $request->get('scale');

                $findQ = $repositoryQ->findOneBy(array('testId' => $_COOKIE['id'], 'id' => $questionId));
                $findT = $repositoryT->findOneBy(array('id' => $_COOKIE['id'], 'userId' => $user->getId()));
                if ($findT && $findQ) {     // pokud nalezne shodu povoli uzivateli editovat zaznam
                    $updateQt = $em->createQueryBuilder()
                            ->update('teststestBundle:Question', 'q')
                            ->set('q.text', '?1')
                            ->set('q.scale', '?2')
                            ->where('q.id = :id')
                            ->setParameter('1', $questionText)
                            ->setParameter('2', $scale)
                            ->setParameter('id', $questionId)
                            ->getQuery();
                    $findA = $repositoryA->findOneBy(array('questionId' => $questionId));
                    if ($findA) {
                        $updateAn = $em->createQueryBuilder()
                                ->update('teststestBundle:Answer', 'a')
                                ->set('a.text', '?3')
                                ->where('a.questionId = :qid')
                                ->setParameter('3', $tAnswer)
                                ->setParameter('qid', $questionId)
                                ->getQuery();
                        $updateQt->execute();
                        $updateAn->execute();
                    }
                }
            }

            $authUT = $repositoryT->findOneBy(array('userId' => $user->getId(), 'id' => $_COOKIE['id']));
            if ($authUT) {
                $id = $_COOKIE['id'];
                $selectTest = $repositoryT->findBy(array('id' => $id));
                $selectQuestion = $em->createQueryBuilder()
                        ->select('q')
                        ->from('teststestBundle:Question', 'q')
                        ->where('q.testId = :test_id')
                        ->setParameter('test_id', $id)
                        ->getQuery()
                        ->getArrayResult();
                $selectAnswer = $repositoryA->findAll();
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'tests' => $selectTest, 'questions' => $selectQuestion, 'answers' => $selectAnswer));
            } else {
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'error' => 'K tomuto testu nemáte přístup'));
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

    public function editNewQuestionAction(Request $request) {
        // nahled testu
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryU = $em->getRepository('teststestBundle:User');
        $repositoryT = $em->getRepository('teststestBundle:Test');
        $repositoryA = $em->getRepository('teststestBundle:Answer');
        $repositoryQ = $em->getRepository('teststestBundle:Question');

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            if ($request->getMethod() == 'POST') {
                $questionId = $request->get('questionId');
                $questionText = trim($request->get('questionText'));
                $tAnswer = trim($request->get('tAnswer'));
                $answerIds = array($request->get('answerId1'), $request->get('answerId2'), $request->get('answerId3'));
                $answers = array(trim($request->get("answer1")), trim($request->get("answer2")), trim($request->get("answer3")));
                $scale = $request->get('scale');

                $findQ = $repositoryQ->findOneBy(array('testId' => $_COOKIE['id'], 'id' => $questionId));
                $findT = $repositoryT->findOneBy(array('userId' => $user->getId(), 'id' => $_COOKIE['id']));
                if ($findT && $findQ) {         // pokud nalezne shodu povoli editovat otazky / odpovedi
                    $updateQt = $em->createQueryBuilder()
                            ->update('teststestBundle:Question', 'q')
                            ->set('q.text', '?1')
                            ->set('q.scale', '?2')
                            ->where('q.id = :id')
                            ->setParameter('1', $questionText)
                            ->setParameter('2', $scale)
                            ->setParameter('id', $questionId)
                            ->getQuery();
                    $updateQt->execute();
                    for ($i = 0; $i < 3; $i++) {
                        if ($i == $tAnswer - 1) {
                            $value = 1;
                        } else {
                            $value = 0;
                        }
                        $updateA = $em->createQueryBuilder()
                                ->update('teststestBundle:Answer', 'a')
                                ->set('a.text', '?1')
                                ->set('a.value', '?2')
                                ->where('a.id = :id')
                                ->setParameter('1', $answers[$i])
                                ->setParameter('2', $value)
                                ->setParameter('id', $answerIds[$i])
                                ->getQuery();
                        $updateA->execute();
                    }
                }
            }

            $authUT = $repositoryT->findOneBy(array('userId' => $user->getId(), 'id' => $_COOKIE['id']));
            if ($authUT) {
                $id = $_COOKIE['id'];
                $selectTest = $repositoryT->findBy(array('id' => $id));
                $selectQuestion = $em->createQueryBuilder()
                        ->select('q')
                        ->from('teststestBundle:Question', 'q')
                        ->where('q.testId = :test_id')
                        ->setParameter('test_id', $id)
                        ->getQuery()
                        ->getArrayResult();
                $selectAnswer = $repositoryA->findAll();
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'tests' => $selectTest, 'questions' => $selectQuestion, 'answers' => $selectAnswer));
            } else {
                return $this->render('teststestBundle:Default:previewTest.html.twig', array('username' => $user, 'error' => 'K tomuto testu nemáte přístup'));
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

    public function gradeSystemAction(Request $request) {
        // grade settings for test confirm
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();                 // vytvoreni Doctrine Entity Manager
        $repositoryU = $em->getRepository('teststestBundle:User');
        $repositoryG = $em->getRepository('teststestBundle:Grade');
        $repositoryT = $em->getRepository('teststestBundle:Test');

        $u = new isSession\exist;
        $user = $u->user($session, $repositoryU);
        if ($user) {
            if ($request->getMethod() == 'POST') {
                $od = array(trim($request->get("od1")), trim($request->get("od2")), trim($request->get("od3")), trim($request->get("od4")), trim($request->get("od5")));
                $do = array(trim($request->get("do1")), trim($request->get("do2")), trim($request->get("do3")), trim($request->get("do4")), trim($request->get("do5")));
                $test_id = $request->get("test_id");

                $findT = $repositoryT->findOneBy(array('userId' => $user->getId(), 'id' => $test_id));
                if ($findT) {
                    // pokud test prislusi uzivateli
                    $gradeTest = $repositoryG->findOneBy(array("testId" => $test_id));
                    if ($gradeTest) {
                        // pokud jiz existuje prevod procent na znamku, tak jej updatuje v else kondici jej nastavi (insert)
                        for ($i = 0; $i < 5; $i++) {
                            $updateG = $em->createQueryBuilder()
                                    ->update('teststestBundle:Grade', 'g')
                                    ->set('g.od', '?1')
                                    ->set('g.do', '?2')
                                    ->where('g.znamka = ?3')
                                    ->setParameter('1', $od[$i])
                                    ->setParameter('2', $do[$i])
                                    ->setParameter('3', $i + 1)
                                    ->getQuery();
                            $updateG->execute();
                        }
                    } else {
                        for ($i = 0; $i < 5; $i++) {
                            $grade = new Grade();
                            $grade->setTestId($test_id);
                            $grade->setOd($od[$i]);
                            $grade->setDo($do[$i]);
                            $grade->setZnamka($i + 1);
                            $em->persist($grade);                                // zpracovani dat
                            $em->flush();
                        }
                    }
                }

                $userId = $user->getId();
                $tests = $em->createQueryBuilder()
                        ->select('t')
                        ->from('teststestBundle:Test', 't')
                        ->where('t.userId = :userId')
                        ->setParameter('userId', $userId)
                        ->getQuery()
                        ->getResult();
                return $this->render('teststestBundle:Default:profile.html.twig', array('name' => ucfirst($user->getName()), 'surname' => ucfirst($user->getSurname()), 'tests' => $tests, "message" => "Procentuální převod na známku byl nastaven"));     // otevreni profilu
            }
        }
        return $this->render('teststestBundle:Default:home.html.twig');
    }

}
