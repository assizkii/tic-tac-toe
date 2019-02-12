<?php

/**
 * Class TicTacGame
 */
class TicTacGame
{

    const AUTHOR_NAME = 'Nickolay Kisluhin';

    /**
     * Available types
     * @var array
     */
    private $types = [
      'X', 'O'
    ];

    private $winFields = [
        [1, 2, 3], [4, 5, 6], [7, 8, 9], [1, 4, 7],
        [2, 5, 8], [3, 6, 9], [1, 5, 9], [3, 5, 7],
    ];

    const STATE_FILE = __DIR__.'/state.json';

    private $defaultBoard = [
        1 => null, 2 => null, 3 => null,
        4 => null, 5 => null, 6 => null,
        7 => null, 8 => null, 9 => null
    ];

    private $stateBoard = [];

    /**
     * Default command list in console
     * @var array
     */
    private $defaultCommandList = [
        1 => 'New game',
        2 => 'Author',
    ];

    private $currentCommandList = [];

    private $currentCommand = null;

    private $currentType = null;

    private $gameId = null;

    /**
     * set id, read state file, open file, draw default commands
     * TicTacGame constructor.
     * @param null $gameId
     */
    public function __construct($gameId = null)
    {

        $this->currentCommandList = $this->defaultCommandList;
        $this->gameId = $gameId;
        $this->readFile();

        $this->drawCommandList();
        $this->waitCommand();
    }

    /**
     * Draw example field
     */
    private function drawExampleField()
    {
        echo 'Cell number: '.PHP_EOL;

        for ($i = 1; $i < 10; $i ++) {
            echo "|$i|";
            if ($i%3 === 0) echo PHP_EOL;
        }

        echo '_________'.PHP_EOL;
        echo PHP_EOL;
    }

    /**
     * Drawing fields 3x3
     */
    private function drawPlayedField()
    {
        $this->drawExampleField();

        foreach ($this->stateBoard as $cell => $cellValue) {

            if (!is_null($cellValue)) {

                echo " $cellValue ";

            }  else {

                echo "|_|";

            }

            if ($cell%3 === 0) echo PHP_EOL;

        }
        echo PHP_EOL;
    }

    /**
     * Make current move
     */
    private function makeMove()
    {
        echo "Enter cell number and type ( X or O) , for example: 3:X" .PHP_EOL;

        $handle = fopen ("php://stdin","r");

        $line = fgets($handle);

        $cell= trim($line);

        if ($this->checkCell($cell)) {

           $this->writeMove($cell);

        }

        $this->checkWin();

    }

    /**
     * Check current move on win
     */
    private function checkWin()
    {
        foreach ($this->winFields as $winFields) {

            foreach ($this->types as $type) {

                $keys = array_keys($this->stateBoard, $type);

                if (count(array_intersect($keys, $winFields)) === 3) {

                    echo "WINNER -  $type".PHP_EOL;
                    exit();

                }

            }

        }

    }

    /**
     * Write correct current more in state file
     * @param $cell - string, for example '1:X'
     */
    private function writeMove($cell)
    {
        $this->stateBoard[$cell[0]] = $cell[2];

        $contentJson = file_get_contents(self::STATE_FILE);
        $content = json_decode($contentJson, true);

        $content[$this->gameId] = $this->stateBoard;

        file_put_contents(self::STATE_FILE, json_encode($content));

        $this->clearScreen();
        $this->drawPlayedField();

    }

    /**
     * Check entered cell on correct
     * @param $cell - entered cell
     * @return bool - cell correct ?
     */
    private function checkCell($cell)
    {
        // check cell on format , '3:X'
        $correctCell = preg_match('/[1-9][\':\'][\'O\'| \'X\']/', $cell, $matches);

        if (!$correctCell) {

            echo "Incorrect cell".PHP_EOL;
            return false;

        } elseif(!empty($this->stateBoard[$cell[0]])) { // cell already use

            echo "This cell already use".PHP_EOL;
            return false;

        } elseif ($this->currentType == $cell[2]) { // current type already make move

            echo $this->currentType. " already make move! ".PHP_EOL;
            return false;

        }

        $this->setCurrentType($cell[2]);

        return true;
    }

    /**
     *  Read state from file
     */
    private function readFile()
    {
        try {

            $this->checkFile();

            $contentJson = file_get_contents(self::STATE_FILE);
            $content = json_decode($contentJson, true);

            if (!is_null($this->gameId) && !isset($content[$this->gameId])) {

                echo "Game not found".PHP_EOL;
                exit();

            } elseif (isset($content[$this->gameId])) {

                $this->currentCommandList = [ 1 => 'Continue', 2 =>'Author'];
                $this->stateBoard = $content[$this->gameId];

            } else {

                $this->gameId = uniqid();
                $this->stateBoard = $this->defaultBoard;

            }

        } catch (Exception $e) {

            echo $e->getMessage().PHP_EOL;

        }
    }

    /**
     * @return bool
     * @throws Exception
     */
    private function checkFile()
    {
        if (!file_exists(self::STATE_FILE)) {

            throw new Exception( "State File don`t exist".PHP_EOL);

        }

        if (!is_readable(self::STATE_FILE)) {

            throw new Exception( "State File don`t read".PHP_EOL);

        }

        if (!is_writable(self::STATE_FILE)) {

            throw new Exception( "State File don`t read".PHP_EOL);

        }

        return true;

    }

    /**
     * Wail enter command
     */
    private function waitCommand()
    {
        $handle = fopen ("php://stdin","r");
        $line = fgets($handle);
        $this->currentCommand = intval(trim($line));

        try {
            $currentTextCommands = $this->currentCommandList[$this->currentCommand];



            $this->clearScreen();

            switch ($currentTextCommands) {

                case 'Author':

                    echo self::AUTHOR_NAME.PHP_EOL;
                    $this->currentCommandList = [ 1 =>'Back'];

                    break;
                case 'New game':
                case 'Continue':

                    $this->currentCommandList = [ 1 => 'Move', 2 =>'End game'];

                    break;

                case 'Move':

                    $this->drawPlayedField();
                    $this->makeMove();

                    break;
                case 'Back':

                    $this->currentCommandList = $this->defaultCommandList;

                    break;
                case 'End game':

                    echo "Good luck!".PHP_EOL;
                    exit();

                default:
                    throw new Exception(  "Unknown command".PHP_EOL);
            }
        }
        catch (Exception $e) {
            echo $e->getMessage().PHP_EOL;
        }
        $this->drawCommandList();
        $this->waitCommand();

    }

    private function setCurrentType($type)
    {
        $this->currentType = $type;
    }

    /**
     * Draw current command list
     */
    private function drawCommandList()
    {
        foreach ($this->currentCommandList as $key => $command) {

            echo $key.'. '.$command.PHP_EOL;

        }

    }

    /**
     * Clear console screen
     */
    private function clearScreen()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

            popen('cls', 'w');

        } else {

            system("clear");

        }
    }
}
