<?php


namespace App\Services;


use DateInterval;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Nette\Utils\DateTime;

class DataManager
{
    const DATA_FILENAME_MASK = "data/vaccination_*.json";
    const FILESIZE_TRESHHOLD = 4200000;

    /**
     * @return mixed
     * @throws Exception
     */
    public function getVaccination()
    {
        $date = (new DateTime)->from('now');
        $datetimeTreshhold = (new DateTime)->from($date);
        $datetimeTreshhold->setTime(8, 0);
        if ($date < $datetimeTreshhold) {
            $date->sub(new DateInterval('PT8H'));
        }
        $filename = self::createFileNameFromDate($date);

        if (file_exists($filename)) {
            $data = self::readDataFromFile($filename);
        } else {
            try {
                $data = (new Covid19Api())->getVaccinationByRegions();
                $jsonData = json_decode($data);
                $filename = self::createFileNameFromDate($jsonData->modified);
                if (!file_exists($filename)) {
                    self::createDirectoryIfNeeded($filename);
                    $result = self::writeDataToFile($filename, $data);
                    if ($result) {
                        self::checkAndDeleteFilesExcept($filename);
                    }
                }

                return $jsonData;

            } catch (GuzzleException | Exception $e) {
                // if web service does not work, use the latest file as a last resort
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
        $writeResult = fwrite($fileHandler, $data);
        $closeResult = fclose($fileHandler);
        return $writeResult && $closeResult;
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
        $files = glob(self::DATA_FILENAME_MASK);
        rsort($files);

        return reset($files);
    }

    /**
     * @param string $date
     * @return string
     * @throws Exception
     */
    private static function createFileNameFromDate(string $date): string
    {
        return
            str_replace(
                '*',
                DateTime::from($date)->format('Y-m-d'),
                self::DATA_FILENAME_MASK);
    }

    private static function checkAndDeleteFilesExcept($filename): void
    {
        $files = glob(self::DATA_FILENAME_MASK);
        rsort($files);
        array_shift($files);   // prepare all for removing except the latest one

        // remove others only if the given file is not suspiciously small
        if (filesize($filename) > self::FILESIZE_TRESHHOLD) {
            foreach ($files as $removing) {
                unlink($removing);
            }
        }
    }

}
