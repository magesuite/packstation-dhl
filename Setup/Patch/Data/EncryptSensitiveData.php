<?php

declare(strict_types=1);

namespace MageSuite\PackstationDhl\Setup\Patch\Data;

class EncryptSensitiveData implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {}

    public function apply(): self
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('core_config_data'), ['config_id', 'value'])
            ->where('path = ?', \MageSuite\PackstationDhl\Helper\Configuration::XML_PATH_CARRIERS_DHL_PACKSTATION_API_KEY);
        $rows = $connection->fetchAll($select);

        foreach ($rows as $row) {
            if (empty($row['value'])) {
                continue;
            }

            $encryptedValue = $this->encryptor->encrypt($row['value']);
            $connection->update(
                $connection->getTableName('core_config_data'),
                ['value' => $encryptedValue],
                ['config_id = ?' => $row['config_id']]
            );
        }

        return $this;
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }
}
