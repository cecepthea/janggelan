<?php namespace system\dragon_fire\controllers;

use system\dragon_fire\interfaces\connection\DataFilter;
use system\dragon_fire\exceptions\DragonHandler;

/*
||***************************************************************************||
|| Class untuk mendapatkan dan mengontrol data yang di dapatkan dari         ||
|| konfigurasi 'config/database.php'. Data tersebut diperlukan untuk proses  ||
|| koneksi atau yang berhubungan dengan Database.                            ||
||***************************************************************************||
||
*/
class DatabaseDataController implements DataFilter
{
    use \register\paths;

    private $finalSecretData;

    public function __construct()
    {
        $this->finalSecretData = [];

        $this->dbSecretController();
    }

    /**
    ***************************************************************************
    * Memberikan data valid yang terstruktur agar mudah digunakan
    *
    * @return   array
    *
    */
    public function data()
    {
        try
        {
            extract($this->dataFilter($this->getData()));

            return [
                'AUTO_CONNECT' => $autoConnect,
                'DB_COLLECTIONS' => $collections,
                'DB_DEFAULT_CONNECTION' => $defaultConnection,
                'DB_HOST' => $properties['DB_HOST'],
                'DB_NAME' => $properties['DB_NAME'],
                'DB_USERNAME' => $properties['DB_USERNAME'],
                'DB_PASSWORD' => $properties['DB_PASSWORD'],
                'PDO_FETCH_STYLE' => $pdoFetchStyle,
            ];
        }
        catch(DragonHandler $e)
        {
            die($e->getError());
        }
    }

    /**
    ***************************************************************************
    * Fungsi untuk mendapatkan data
    *
    * @return   array
    *
    * @throws   \system\dragon_fire\exceptions\DragonHandler
    *
    */
    public function getData()
    {
        if(!is_array($data = require($this->getPath()['config'].'/database.php')))
        {
            Throw new DragonHandler('Data \'config/database.php\' harus berupa array!');
        }

        return $data;
    }

    /**
    ***************************************************************************
    * Melakukan filter data sebelum melakukan koneksi
    *
    * @param    array   $data
    * @return   array
    *
    */
    public function dataFilter(array $data)
    {
        $defaultConnection = $data['default_connection'];
        $pdoFetchStyle = $data['pdo_fetch_style'];
        $collections = $data['connections'];
        $autoConnect = $data['auto_connect'];
        $properties = $collections[$defaultConnection];

        is_string($defaultConnection) ? $defaultConnection = $defaultConnection : $defaultConnection = 'mysql';
        is_bool($autoConnect) ? $autoConnect = $autoConnect : $autoConnect = FALSE;

        return compact(
            'defaultConnection', 'pdoFetchStyle', 'collections', 'properties', 'autoConnect'
        );
    }

    public function dbSecretController()
    {
        $secretData = rtrim(preg_replace('/\n/', '\n', file_get_contents(
            $this->getPath()['backend'].'/.secrets/.db')), '\n');
        $explodeData = explode('\n', $secretData);

        foreach($explodeData as $exp)
        {
            $pre = explode(':', $exp);
            $this->finalSecretData[$pre[0]] = $pre[1];
        }
    }

    public function DB($index)
    {
        return isset($this->finalSecretData['DB_'.$index]) ?
            $this->finalSecretData['DB_'.$index] : FALSE;
    }
}
