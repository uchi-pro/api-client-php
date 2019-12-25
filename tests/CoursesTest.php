<?php

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Courses\Course;
use UchiPro\Courses\Criteria;
use UchiPro\Identity;

class CoursesTest extends TestCase
{
    /**
     * @var Identity
     */
    private $identity;

    public function setUp()
    {
        $url = getenv('UCHIPRO_URL');
        $login = getenv('UCHIPRO_LOGIN');
        $password = getenv('UCHIPRO_PASSWORD');
        $accessToken = getenv('UCHIPRO_ACCESS_TOKEN');

        if (!empty($accessToken)) {
            $this->identity = Identity::createByAccessToken($url, $accessToken);
        } else {
            $this->identity = Identity::createByLogin($url, $login, $password);
        }
    }

    /**
     * @return ApiClient
     */
    public function getApiClient()
    {
        return ApiClient::create($this->identity);
    }

    public function testGetCourses()
    {
        $criteria = new Criteria();
        $criteria->withLessons = true;
        $courses = $this->getApiClient()->courses()->findBy($criteria);

        $this->assertTrue(is_array($courses));

        $lessonsCount = array_reduce($courses, function ($total, Course $course) {
            return $total + count($course->lessons);
        }, 0);

        $this->assertTrue($lessonsCount > 0);

        $hours = 0;
        foreach ($courses as $course) {
            if ($course->academicPlan) {
                foreach ($course->academicPlan->items as $item) {
                    $hours += $item->hours;
                }
            }
        }
        $this->assertTrue($hours > 0);
    }
}
