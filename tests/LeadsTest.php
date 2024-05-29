<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use UchiPro\Courses\Course;
use UchiPro\Users\User;

class LeadsTest extends TestCase
{
    public function testSaveMinimalLead()
    {
        $leadsApi = $this->getApiClient()->leads();

        $stamp = date('YmdHms');

        $lead = $leadsApi->newLead();
        $lead->email = "u$stamp@uchi.pro";

        $lead = $leadsApi->save($lead);
        $this->assertNotEmpty($lead->id);
    }

    public function testSaveFullLead()
    {
        $me = $this->getApiClient()->users()->getMe();

        $leadsApi = $this->getApiClient()->leads();

        $stamp = date('YmdHms');

        $lead = $leadsApi->newLead();
        $lead->number = "$stamp";
        $lead->contactPerson = "Гражданин $stamp";
        $lead->email = "u$stamp@uchi.pro";
        $lead->phone = "+7$stamp";
        $lead->courses = $this->selectMyCoursesForLead($me);

        $mainComment = $leadsApi->newComment(null, 'Первый комментарий.');
        $lead = $leadsApi->save($lead, $mainComment);

        $additionalComment = $leadsApi->newComment(null, "Дополнительный комментарий.\nДля тестирования переносов.");
        $additionalComment = $leadsApi->saveLeadComment($lead, $additionalComment);

        $this->assertNotEmpty($additionalComment->id);
    }

    /**
     * @param User $me
     *
     * @return array|Course[]
     */
    private function selectMyCoursesForLead(User $me): array
    {
        $coursesApi = $this->getApiClient()->courses();
        $courses = [];
        $i = 0;
        $coursesCriteria = $coursesApi->newCriteria();
        $coursesCriteria->vendor = $me->vendor;
        foreach ($coursesApi->findBy($coursesCriteria) as $course) {
            $courses[] = $course;
            $i++;
            if ($i === 3) {
                break;
            }
        }
        return $courses;
    }
}
