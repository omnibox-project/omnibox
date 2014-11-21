<?php
namespace Uberstead\Helper;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;
use Uberstead\Helper\ProcessHelper;
use Uberstead\Helper\QuestionHelper;

class CliHelper
{
    /**
     * @var HelperSet
     */
    private $helperset;

    /**
     * @var ProcessHelper
     */
    private $processHelper;

    /**
     * @var QuestionHelper
     */
    private $questionHelper;

    /**
     * @var InputInterface
     */
    private $inputInterface;

    /**
     * @var OutputInterface
     */
    private $outputInterface;

    function __construct()
    {
        $this->questionHelper = new QuestionHelper($this);
        $this->processHelper = new ProcessHelper($this);
    }

    /**
     * @return ProcessHelper
     */
    public function getProcessHelper()
    {
        return $this->processHelper;
    }

    /**
     * @return QuestionHelper
     */
    public function getQuestionHelper()
    {
        return $this->questionHelper;
    }

    /**
     * @return HelperSet
     */
    public function getHelperset()
    {
        return $this->helperset;
    }

    /**
     * @param HelperSet $helperset
     */
    public function setHelperset($helperset)
    {
        $this->helperset = $helperset;
    }

    /**
     * @return InputInterface
     */
    public function getInputInterface()
    {
        return $this->inputInterface;
    }

    /**
     * @param InputInterface $inputInterface
     */
    public function setInputInterface($inputInterface)
    {
        $this->inputInterface = $inputInterface;
    }

    /**
     * @return OutputInterface
     */
    public function getOutputInterface()
    {
        return $this->outputInterface;
    }

    /**
     * @param OutputInterface $outputInterface
     */
    public function setOutputInterface($outputInterface)
    {
        $this->outputInterface = $outputInterface;
    }
}
