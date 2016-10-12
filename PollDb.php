<?php
namespace nikser\poll;

use yii;

class PollDb
{

    public function isPollExist($pollName)
    {
        return (bool)Yii::$app->db
            ->createCommand('SELECT count(*) FROM `polls` WHERE `poll_name`=:pollName')
            ->bindParam(':pollName', $pollName)
            ->queryOne();
    }

    public function setVoicesData($pollName, $answerOptions)
    {
        for ($i = 0; $i < count($answerOptions); $i++) {
            Yii::$app->db->createCommand()
                ->insert('voices_of_poll', [
                    'poll_name' => $pollName,
                    'answers' => $answerOptions[$i],
                    'value' => 0
                ])
                ->execute();
        }
    }

    /**
     * @param array $pollName
     * @return mixed
     */
    public function getVoicesData($pollName)
    {
        return Yii::$app->db
            ->createCommand('SELECT * FROM voices_of_poll WHERE poll_name=:pollName')
            ->bindParam(':pollName', $pollName)
            ->queryAll();
    }

    public function updateAnswers($pollName, $voice, $answerOptions)
    {
        if (isset($_POST['VoicesOfPoll']['voice'])) {
            Yii::$app->db
                ->createCommand("UPDATE voices_of_poll SET value=value +1 WHERE poll_name=:name AND answers=:answers")
                ->bind(':name', $pollName)
                ->bind(':answers', $answerOptions[$voice])
                ->execute();
        }
    }

    public function updateUsers($pollName)
    {
        $userId = Yii::$app->getUser()->getId() ?: 0;

        $pollData = Yii::$app->db
            ->createCommand('SELECT * FROM polls WHERE poll_name=:pollName')
            ->bindParam(':pollName', $pollName)
            ->queryOne();

        Yii::$app->db->createCommand()
            ->insert('users_id', [
                'poll_id' => $pollData['id'],
                'user_id' => $userId
            ])
            ->execute();
    }

    public function isVote($pollName)
    {
        $pollData = Yii::$app->db
            ->createCommand('SELECT * FROM polls WHERE poll_name=:pollName')
            ->bindParam(':pollName', $pollName)
            ->queryOne();

        $result = Yii::$app->db
            ->createCommand('SELECT * FROM users_id WHERE user_id=:userId AND poll_id=:pollId')
            ->bindParam(':userId', Yii::$app->user->getId() ?: 0)
            ->bindParam(':pollId', $pollData['id'])
            ->queryOne();

        return (bool)$result;
    }

    public function createTables()
    {
        $db = Yii::$app->db;
        $db->createCommand("
                        CREATE TABLE IF NOT EXISTS `users_id` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `poll_id` int(11) NOT NULL,
                        `user_id` int(11) NOT NULL,
                        PRIMARY KEY (`id`),
                        KEY `poll_id` (`poll_id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8"
        )->execute();

        $db->createCommand("
                        CREATE TABLE IF NOT EXISTS `polls` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `poll_name` varchar(500) NOT NULL,
                        `answer_options` text NOT NULL,
                        PRIMARY KEY (`id`),
                        KEY `poll_name` (`poll_name`(255))
                        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8"
        )->execute();

        $db->createCommand("
                        CREATE TABLE IF NOT EXISTS `voices_of_poll` (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `poll_name` varchar(500) NOT NULL,
                        `answers` varchar(100) CHARACTER SET utf8mb4 NOT NULL,
                        `value` int(11) NOT NULL,
                        PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8"
        )->execute();
    }

    public function isTableExists()
    {
        return (bool)Yii::$app->db->createCommand("SHOW TABLES LIKE 'polls'")->queryOne();
    }

}