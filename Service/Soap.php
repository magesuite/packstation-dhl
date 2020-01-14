<?php

namespace MageSuite\PackstationDhl\Service;

class Soap
{
    const DHL_PACKSTATION_CIG_SANDBOX_ENDPOINT = 'https://cig.dhl.de/services/sandbox/soap';
    const DHL_PACKSTATION_CIG_LIVE_ENDPOINT = 'https://cig.dhl.de/services/production/soap';
    const DHL_PACKSTATION_WSDL = 'https://cig.dhl.de/cig-wsdls/com/dpdhl/wsdl/standortsuche-api/1.1/standortsuche-api-1.1.wsdl';

    const LOCAL_WSDL_DIR = 'dhl';
    const LOCAL_WSDL_FILE = '/dhl/dhl.wsdl';

    /**
     * @var \Magento\Framework\Webapi\Soap\ClientFactory
     */
    protected $soapFactory;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $directoryWrite;

    /**
     * @var \MageSuite\PackstationDhl\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    protected $soapClient = null;

    public function __construct(
        \Magento\Framework\Webapi\Soap\ClientFactory $soapFactory,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem $filesystem,
        \MageSuite\PackstationDhl\Helper\Configuration $configuration,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->soapFactory = $soapFactory;
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->directoryWrite = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
        $this->configuration = $configuration;
        $this->logger = $logger;

        $this->directoryWrite = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR);
    }

    public function getClient()
    {
        if ($this->soapClient === null) {

            $login = $this->configuration->getApiLogin();
            $password = $this->configuration->getApiPassword();
            $wsdlFile = $this->getWsdlFile();

            if (empty($login) || empty($password) || empty($wsdlFile)) {
                $this->logger->error('DHL Packstation soap client error: Missing credentials');
                return null;
            }

            $location = $this->configuration->getMode() == \MageSuite\PackstationDhl\Model\Config\Source\Mode::MODE_LIVE ?
                self::DHL_PACKSTATION_CIG_LIVE_ENDPOINT :
                self::DHL_PACKSTATION_CIG_SANDBOX_ENDPOINT;

            $this->soapClient = $this->soapFactory->create($wsdlFile, [
                'login' => $login,
                'password' => $password,
                'location' => $location,
                'soap_version' => SOAP_1_1,
                'trace' => true
            ]);
        }

        return $this->soapClient;
    }

    protected function getWsdlFile()
    {
        $wsdlPath = $this->directoryList->getPath(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR) . self::LOCAL_WSDL_FILE;
        $fileDriver = $this->directoryWrite->getDriver();

        if (!$fileDriver->isExists($wsdlPath)) {
            try {
                $dir = $fileDriver->getParentDirectory($wsdlPath);

                if (!$fileDriver->isExists($dir)) {
                    $this->directoryWrite->create(self::LOCAL_WSDL_DIR);
                }

                $wsdl = $fileDriver->fileGetContents(self::DHL_PACKSTATION_WSDL);
                $fileDriver->filePutContents($wsdlPath, $wsdl);
            } catch (\Exception $e) {
                $this->logger->error('DHL Packstation soap client error: ' . $e->getMessage());
                return null;
            }
        }

        return $wsdlPath;
    }
}
