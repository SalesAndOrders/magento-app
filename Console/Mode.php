<?php
namespace SalesAndOrders\FeedTool\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;


class Mode extends Command
{
    const SHOW = 'show';
    const SET_DEVELOP = 'set-develop';
    const SET_STAGING = 'set-staging';
    const SET_DEFAULT = 'set-default';

    /**
     * @var \SalesAndOrders\FeedTool\Helper\Config
     */
    protected $_helper;

    /**
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     *
     * @param \SalesAndOrders\FeedTool\Helper\Config $helper
     */
    public function __construct(
        \SalesAndOrders\FeedTool\Helper\Config $helper
    )
    {
        $this->_helper = $helper;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('sando:mode');
        $this->setDescription('Define API mode');

        $options = [
            new InputOption(
                self::SHOW,
                null,
                InputOption::VALUE_NONE
            ),
            new InputOption(
                self::SET_DEFAULT,
                null,
                InputOption::VALUE_NONE
            ),
            new InputOption(
                self::SET_DEVELOP,
                null,
                InputOption::VALUE_NONE
            ),
            new InputOption(
                self::SET_STAGING,
                null,
                InputOption::VALUE_NONE
            )
        ];

        $this->setDefinition($options);

        parent::configure();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // set specefied mode
        if ($param = $input->getOption(self::SHOW)) {
            $output->writeln("Mode: " . $this->_helper->getDeployModeTextValue() );
        }elseif($param = $input->getOption(self::SET_DEFAULT)) {
            $this->_helper->setDeployMode(0);
            $output->writeln("Mode set to: " . $this->_helper->getDeployModeTextValue() );
        }elseif($param = $input->getOption(self::SET_DEVELOP)) {
            $this->_helper->setDeployMode(1);
            $output->writeln("Mode set to: " . $this->_helper->getDeployModeTextValue() );
        }elseif($param = $input->getOption(self::SET_STAGING)) {
            $this->_helper->setDeployMode(2);
            $output->writeln("Mode set to: " . $this->_helper->getDeployModeTextValue() );
        }else{
            $output->writeln("None");
        }
        return $this;
    }
}
