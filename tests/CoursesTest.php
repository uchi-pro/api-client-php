<?php

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Courses\CourseFeatures;
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
        $coursesApi = $this->getApiClient()->courses();
        $coursesCriteria = $coursesApi->createCriteria();
        $courses = $coursesApi->findBy($coursesCriteria);

        $this->assertTrue(is_array($courses));

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

    public function testFindCourseById()
    {
        $coursesApi = $this->getApiClient()->courses();

        $courses = $coursesApi->findBy();

        if (empty($courses[0])) {
            $this->markTestSkipped(
              'Курс для теста не найден.'
            );
        }

        $course = $courses[0];
        $foundCourse = $coursesApi->findById($course->id);
        $this->assertEquals($course->id, $foundCourse->id);
    }

    public function testGetCourseFeatures()
    {
        $coursesApi = $this->getApiClient()->courses();
        $coursesCriteria = $coursesApi->createCriteria();
        $courses = $coursesApi->findBy($coursesCriteria);

        if (empty($courses[0])) {
            $this->markTestSkipped('Курс для теста не найден.');
        }
        $course = $courses[0];

        $courseFeatures = $coursesApi->getCourseFeatures($course);
        $this->assertInstanceOf(CourseFeatures::class, $courseFeatures);
    }
}
