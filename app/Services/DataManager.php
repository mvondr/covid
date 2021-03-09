<?php


namespace App\Services;


use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Nette\Utils\DateTime;

class DataManager
{
    const DATA_FILENAME_TEMPLATE = "data/vaccination_%DATE%.json";

    /**
     * @return mixed
     * @throws Exception
     */
    static public function getVaccination()
    {
        $filename = str_replace(
            '%DATE%',
            DateTime::from('now')->format('Y-m-d'),
            self::DATA_FILENAME_TEMPLATE);

        if (file_exists($filename)) {
            $data = self::readDataFromFile($filename);
        } else {
            try {
                // read from web service
                $data = (new Covid19Mzcr())->getVaccinationByRegions();

                // write it to file
                if (!file_exists($filename)) {
                    self::createDirectoryIfNeeded($filename);
                    self::writeDataToFile($filename, $data);
                }
            } catch (GuzzleException | Exception $e) {
                // if web service does not work, use the latest file
                $filename = self::getLatestFilename();
                if (file_exists($filename)) {
                    $data = self::readDataFromFile($filename);
                } else {
                    throw new Exception("No data.");
                }
            }
        }

        return json_decode($data);
    }

    /**
     * @param string $filename
     * @return string
     */
    static private function readDataFromFile(string $filename): string
    {
        $fileHandler = fopen($filename, 'r');
        $data = fread($fileHandler, filesize($filename));
        fclose($fileHandler);

        return $data;
    }

    /**
     * @param string $filename
     * @param string $data
     * @return bool
     */
    private static function writeDataToFile(string $filename, string $data): bool
    {
        $fileHandler = fopen($filename, 'w');
        fwrite($fileHandler, $data);

        return fclose($fileHandler);
    }

    /**
     * @param string $filename
     * @return bool true if directory is successfully created or exists
     */
    private static function createDirectoryIfNeeded(string $filename): bool
    {
        $dirname = dirname($filename);
        if (!is_dir($dirname)) {
            return mkdir($dirname, 0755, true);
        }

        return true;
    }

    /**
     * @return string
     */
    private static function getLatestFilename(): string
    {
        $filenameWithWildcard = str_replace('%DATE%', '*', self::DATA_FILENAME_TEMPLATE);
        $files = glob($filenameWithWildcard);
        rsort($files);

        return reset($files);
    }
}
