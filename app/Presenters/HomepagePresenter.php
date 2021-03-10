<?php /** @noinspection PhpUnused */

namespace App\Presenters;

use App\Entities\Demography;
use App\Entities\VaccinationGroup;
use App\Services\DataManager;
use Exception;
use Nette\Utils\DateTime;
use Nette;


final class HomepagePresenter extends Nette\Application\UI\Presenter
{
    /**
     * @var DataManager
     */
    private $dataManager;

    public function __construct(DataManager $dataManager)
    {
        parent::__construct();
        $this->dataManager = $dataManager;
    }

    public function renderDefault($date)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $bulkData = $this->dataManager->getVaccination();
        $vaccinationGroup = new VaccinationGroup();
        /** @noinspection PhpUnhandledExceptionInspection */
        $vaccinationGroup->init($bulkData);

        if ($date) {
            try {
                $dateObj = DateTime::from($date);
            } catch (Exception $e) {
                $dateObj = $vaccinationGroup->modified;
            }
        } else {
            $dateObj = $vaccinationGroup->modified;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $sumAll = $vaccinationGroup->sumAll($dateObj);
        $this->template->modified = $sumAll->modified;
        $this->template->source = $sumAll->source;
        $this->template->prvnich_davek = $sumAll->data[0]->prvnich_davek;
        $this->template->druhych_davek = $sumAll->data[0]->druhych_davek;
        $this->template->celkem_davek = $sumAll->data[0]->prvnich_davek + $sumAll->data[0]->druhych_davek;

        /** @noinspection PhpUnhandledExceptionInspection */
        $groupByAgeCategory = $vaccinationGroup->groupByAgeCategory($dateObj);
        $this->template->groupByAgeCategory = $groupByAgeCategory->data;
        $this->template->dateList = $vaccinationGroup->getDateList();
        $this->template->selectedDate = $dateObj->format('Y-m-d');
        $this->template->inputDate = $date;
        $this->template->allPeople = Demography::countPeopleExceptOf(Demography::CATEGORY_0_17)
            - $sumAll->data[0]->druhych_davek
            + $groupByAgeCategory->data[VaccinationGroup::CATEGORY_0_17]->druhych_davek;
        $this->template->demography = Demography::countPeopleByAgeCategories();
    }
}
