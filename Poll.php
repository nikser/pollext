<?php
namespace nikser\poll;

use yii;
use yii\base\Widget;
use yii\helpers\Html;

class Poll extends Widget
{

    public $answerOptions = array();
    public $answers = array();
    public $sumOfVoices = 0;
    public $isVote;

    private $pollName = '';
    private $answerOptionsData;
    private $pollData;
    private $params = array(
        'maxLineWidth' => 300,
        'backgroundLinesColor' => '#D3D3D3',
        'linesHeight' => 15,
        'linesColor' => '#4F9BC7'
    );

    public function setPollName($name)
    {
        $this->pollName = $name;
    }

    public function getDbData()
    {
        $this->pollData = Yii::$app->db
            ->createCommand('SELECT * FROM polls WHERE poll_name=:pollName')
            ->bindParam(':pollName', $this->pollName)
            ->queryOne();

        $this->answerOptionsData = unserialize($this->pollData['answer_options']);
    }

    private function setDbData()
    {
        Yii::$app->db->createCommand()
            ->insert('polls', [
                'poll_name' => $this->pollName,
                'answer_options' => $this->answerOptionsData
            ])
            ->execute();
    }

    public function setParams($params)
    {
        $this->params = array_merge($this->params, $params);
    }

    public function getParams($param)
    {
        return $this->params[$param];
    }

    public function init()
    {
        parent::init();

        $pollDB = new PollDb;
        $pollDB->isTableExists() || $pollDB->createTables();

        if ($this->answerOptions) {
            $this->answerOptionsData = serialize($this->answerOptions);
        }
        if (!$pollDB->isPollExist($this->pollName)) {
            $this->setDbData();
            $pollDB->setVoicesData($this->pollName, $this->answerOptions);
        }

        if (Yii::$app->request->isAjax
            && isset($_POST['VoicesOfPoll']['voice'])
            && isset($_POST['poll_name'])
            && $_POST['poll_name'] == $this->pollName
        ) {
            $pollDB->updateAnswers($this->pollName, $_POST['VoicesOfPoll']['voice'], $this->answerOptions);
            $pollDB->updateUsers($this->pollName);
        }

        $this->getDbData();
        $this->answers = $pollDB->getVoicesData($this->pollName);

        foreach ($this->answers as $answer) {
            $this->sumOfVoices += $answer['value'];
        }

        $this->isVote = $pollDB->isVote($this->pollName);
    }

    public function run()
    {
        return $this->render('index', [
            'pollData' => $this->pollData,
            'answersData' => $this->answers,
            'params' => $this->params,
            'model' => new VoicesOfPoll,
            'answers' => $this->answerOptions,
            'sumOfVoices' => $this->sumOfVoices,
            'isVote' => $this->isVote,
        ]);
    }

}