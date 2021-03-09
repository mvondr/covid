<?php


namespace App\Entities;

class Demography
{
    const CATEGORY_0_17 = '0-17';

    /** Demography in the Czech Republic
     * Return array of age categories with number of people in each category
     * @return int[]
     */
    static public function countPeopleByAgeCategories(): array
    {
        return
            [
                '0-17' => 1975121,
                '18-24' => 683347,
                '25-29' => 668977,
                '30-34' => 725191,
                '35-39' => 786975,
                '40-44' => 937403,
                '45-49' => 787035,
                '50-54' => 691967,
                '55-59' => 628207,
                '60-64' => 678960,
                '65-69' => 679942,
                '70-74' => 589873,
                '75-79' => 383895,
                '80+' => 432907
            ];
    }

    /**
     * Return number of all people in the Czech Republic
     * @return int
     */
    static public function countAllPeople():int {
        return array_sum(self::countPeopleByAgeCategories());
    }

    /**
     * Return number of all people in the Czech Republic except given category
     * @param string $exceptOf
     * @return int
     */
    static public function countPeopleExceptOf(string $exceptOf):int {
        return self::countAllPeople() - self::countPeopleByAgeCategories()[$exceptOf];
    }
}
