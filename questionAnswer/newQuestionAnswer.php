<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of questionAnswer
 *
 * @author Štefan Prokop
 */

namespace tests\testBundle\questionAnswer;

use tests\testBundle\Controller;
use Doctrine\ORM\EntityManager;
use tests\testBundle\Entity\Question;
use tests\testBundle\Entity\Answer;

class newQuestionAnswer extends Controller\DefaultController {

    public function normalQuestion(EntityManager $em, $scale, $testId, $questionText, $tAnswer, $answers) {
        $question = new Question();                         // vytvori tridu User

        $question->setTestId($testId);                      // ukladani promennych do DB
        $question->setScale($scale);
        $question->setText($questionText);
        $question->setType(0);                      // 0 = otazka + 3 moznosti na vyber

        $em->persist($question);                                // zpracovani dat
        $em->flush();                                       // zapis dat do DB

        $questionId = $question->getId();
        for ($i = 0; $i < 3; $i++) {
            if ($i == $tAnswer - 1) {
                $value = 1;
            } else {
                $value = 0;
            }

            $answ = new Answer();

            $answ->setQuestionId($questionId);
            $answ->setText($answers[$i]);
            $answ->setValue($value);

            $em->persist($answ);                                // zpracovani dat
            $em->flush();                                       // zapis dat do DB
        }
    }

    public function questionAnswer(EntityManager $em, $testId, $scale, $questionText, $tAnswer) {
        $question = new Question();                         // vytvori tridu User

        $question->setTestId($testId);                      // ukladani promennych do DB
        $question->setScale($scale);
        $question->setText($questionText);
        $question->setType(1);                      // 1 = otazka + text box pro odpoved

        $em->persist($question);                                // zpracovani dat
        $em->flush();                                       // zapis dat do DB

        $questionId = $question->getId();

        $answ = new Answer();

        $answ->setQuestionId($questionId);
        $answ->setText($tAnswer);
        $answ->setValue(1);                         // tato odpoved je vzdy spravna

        $em->persist($answ);                                // zpracovani dat
        $em->flush();
    }

}
