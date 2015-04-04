<?php

namespace tests\testBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use tests\testBundle\Modals\fillTest;
use tests\testBundle\isSession;
use tests\testBundle\Entity\Testing;

class TestController extends Controller {

    public function fillTestAction(Request $request) {
        // test page for students
        //$starttime = microtime(true);   // mereni casu DB - pro select testu vytvorit v DB radek
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();
        $repositoryT = $em->getRepository('teststestBundle:Test');
        $repositoryAn = $em->getRepository('teststestBundle:Answer');

        // zamichani otazek a odpovedi
        function shuffle_assoc($array) {
            $shuffled_array = array();
            $shuffled_keys = array_keys($array);
            shuffle($shuffled_keys);

            foreach ($shuffled_keys as $shuffled_key) {
                $shuffled_array[$shuffled_key] = $array[$shuffled_key];
            }
            return $shuffled_array;
        }

        $starttimeL = microtime(true);      // testovaci ucely - rychlost aplikace
        $t = new isSession\exist;
        //$endtimeL = microtime(true);
        //$durationL = ($endtimeL - $starttimeL) * 1000; // milisukundy
        //setcookie("login_time", $durationL);
        $isTest = $t->test($session, $repositoryT);

        if ($request->getMethod() == 'POST') {
            $code = $request->get('code');      // zadany kod testu
            $test = $repositoryT->findOneBy(array('code' => $code));
            if ($test) {
                $selectTest = $em->createQueryBuilder()
                        ->select('t')
                        ->from('teststestBundle:Test', 't')
                        ->where('t.code = :code')
                        ->setParameter('code', $code)
                        ->getQuery()
                        ->getArrayResult();
                for ($i = 0; $i < count($selectTest); $i++) {
                    $selectQuestion = $em->createQueryBuilder()
                            ->select('q')
                            ->from('teststestBundle:Question', 'q')
                            ->where('q.testId = :test_id')
                            ->setParameter('test_id', $selectTest[$i]['id'])
                            ->getQuery()
                            ->getArrayResult();
                }
                $selectAnswer = $repositoryAn->findAll();
                $fillTest = new fillTest();
                $fillTest->setCode($code);
                $session->set('fillTest', $fillTest);
                $sq = shuffle_assoc($selectQuestion);
                $sa = shuffle_assoc($selectAnswer);
                //$endtime = microtime(true);
                //$duration = $endtime - $starttime;
                //setcookie("select_test_time", $duration);
                return $this->render('teststestBundle:Test:fillTest.html.twig', array('code' => $code, 'test' => $selectTest, 'questions' => $sq, 'answers' => $sa));
            } else {
                return $this->render('teststestBundle:Test:testLogin.html.twig', array('error' => 'Vámi zadaný kód testu neexistuje'));
            }
        } else if ($isTest) {
            // pokud refreshoval stranku
            $selectTest = $em->createQueryBuilder()
                    ->select('t')
                    ->from('teststestBundle:Test', 't')
                    ->where('t.code = :code')
                    ->setParameter('code', $isTest->getCode())
                    ->getQuery()
                    ->getArrayResult();
            for ($i = 0; $i < count($selectTest); $i++) {
                $selectQuestion = $em->createQueryBuilder()
                        ->select('q')
                        ->from('teststestBundle:Question', 'q')
                        ->where('q.testId = :test_id')
                        ->setParameter('test_id', $selectTest[$i]['id'])
                        ->getQuery()
                        ->getArrayResult();
            }
            $selectAnswer = $repositoryAn->findAll();

            $sq = shuffle_assoc($selectQuestion);
            $sa = shuffle_assoc($selectAnswer);

            return $this->render('teststestBundle:Test:fillTest.html.twig', array('code' => $isTest->getCode(), 'test' => $selectTest, 'questions' => $sq, 'answers' => $sa));
        } else {
            return $this->render('teststestBundle:Test:testLogin.html.twig');
        }
    }

    public function testConfirmAction(Request $request) {
        // odeslani testu k overeni
        $starttime = microtime(true);   // mereni casu DB
        $session = $this->getRequest()->getSession();
        $em = $this->getDoctrine()->getManager();
        $repositoryAn = $em->getRepository('teststestBundle:Answer');
        $repositoryT = $em->getRepository('teststestBundle:Test');
        $repositoryG = $em->getRepository('teststestBundle:Grade');
        $userAnswers = array();
        $foreach = 0;

        $t = new isSession\exist;
        $isTest = $t->test($session, $repositoryT);
        if ($isTest) {
            $test_id = $isTest->getId();
            if ($request->getMethod() == 'POST') {
                $name = $request->get('nameSurname');
                $selectTest = $em->createQueryBuilder()
                        ->select('t')
                        ->from('teststestBundle:Test', 't')
                        ->where('t.code = :code')
                        ->setParameter('code', $isTest->getCode())
                        ->getQuery()
                        ->getArrayResult();

                $email = $selectTest[0]['email'];
                // selectuje se email ucitele

                for ($i = 0; $i < count($selectTest); $i++) {
                    $selectQuestion = $em->createQueryBuilder()
                            ->select('q')
                            ->from('teststestBundle:Question', 'q')
                            ->where('q.testId = :test_id')
                            ->setParameter('test_id', $selectTest[$i]['id'])
                            ->getQuery()
                            ->getArrayResult();
                }
                $selectAnswer = $repositoryAn->findAll();
                foreach ($selectQuestion as $question) {
                    $question_id = $question['id'];
                    $user_answer = $request->get($question_id);
                    $userAnswers[$foreach] = strtolower($user_answer);
                    $foreach++;
                }
                $grade = $repositoryG->findBy(array('testId' => $test_id));
                $session->clear();

                $subject = "Test";
                $headers = "FROM: testy@online.cz" . "\r\n";
                $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
                $body = $this->renderView("teststestBundle:Test:emailLayout.html.twig", array('user_answers' => $userAnswers, 'test' => $selectTest, 'questions' => $selectQuestion, 'answers' => $selectAnswer, 'name' => $name, 'grade' => $grade));
                $session->set("confirmTest", $body);
                mail($email, $subject, $body, $headers);        // odeslani uciteli emailu s vysledky

                $endtime = microtime(true);
                $duration = ($endtime - $starttime) * 1000;
                // save testing data to DB
                /*
		  $testing = new Testing();
                $testing->setLoginTime($_COOKIE["login_time"]);
                $testing->setSelectTestTime($_COOKIE["select_test_time"]);
                $testing->setTconfirmTime($duration);
                $em->persist($testing);                                // zpracovani dat
                $em->flush();  */                                     // zapis dat do DB

                return $this->render('teststestBundle:Test:testConfirm.html.twig', array('user_answers' => $userAnswers, 'test' => $selectTest, 'questions' => $selectQuestion, 'answers' => $selectAnswer, 'name' => $name, 'grade' => $grade));
            }
        } else {
            return $this->render('teststestBundle:Test:testLogin.html.twig');
        }
        return $this->render('teststestBundle:Test:testLogin.html.twig');
    }

    public function sendSEAction(Request $request) {
        // odeslani vysledku zakovi na email
        if ($request->getMethod() == 'POST') {
            $session = $this->getRequest()->getSession();
            $email = $request->get('email');        // nacteni zpravy ze session
            $body = $session->get("confirmTest");
            $subject = "Výsledky testu";
            $headers = "FROM: testy@online.cz" . "\r\n";
            $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";

            mail($email, $subject, $body, $headers);
            if (@mail) {
                return $this->render('teststestBundle:Default:home.html.twig', array('message' => "Email byl úspěšně odeslán na zadanou emailovou adresu"));
            } else {
                return $this->render('teststestBundle:Default:home.html.twig', array('message' => "Email se nepodařilo odeslat"));
            }
        }
        return $this->render('teststestBundle:Test:testLogin.html.twig');
    }

}
