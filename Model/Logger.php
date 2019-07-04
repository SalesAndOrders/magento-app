<?php

namespace SalesAndOrders\FeedTool\Model;

use \Psr\Log\LoggerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;

class Logger {

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    protected $customLogger = null;

    /**
     * @var File
     */
    protected $file;

    private $logFolder = '/log/';

    private $logDateFolder = '/';

    public $logPath = [];

    public function __construct(
        LoggerInterface $logger,
        DirectoryList $directoryList,
        File $file
    )
    {
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->file = $file;
    }

    public function create($log_name = 'log_file', $fileFolder = '', $type = 'all')
    {
        $fileFolder = $fileFolder ? '/' . $fileFolder : '';
        $this->logDateFolder = '/' . date('Y') . '/' . date('m') . '/' . date('d');
        $this->logFolder = '/log/' . $type . $this->logDateFolder . $fileFolder;

        $pubSmlnkFolred = 'log-smlnk' . $this->logDateFolder;
        $logfile = '/' . $log_name . '_' . date('H_i_s');
        $this->file->mkdir($this->directoryList->getPath('var') . $this->logFolder, 0775);
        $writer = new \Zend\Log\Writer\Stream(BP . '/var' . $this->logFolder . $logfile . '.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $this->customLogger = $logger;
        $this->logPath[] = '/' . $pubSmlnkFolred . $logfile . '.log';
        return $logger;
    }

    public function log($messageData)
    {
        if (!$this->customLogger) {
            return false;
        }

        if (is_array($messageData) || is_object($messageData)) {
            $this->customLogger->info('Log DATA:');
            foreach ($messageData as $key => $value) {
                $this->customLogger->info('Key: ' . $key . ' Value: ' . $value);
            }
            $this->customLogger->info('Log DATA end');
        }elseif($messageData){
            $this->customLogger->info('Log info: ' . $messageData);
        }

        return $this->logPath;
    }

}