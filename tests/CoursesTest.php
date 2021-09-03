<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use UchiPro\ApiClient;
use UchiPro\Courses\Course;
use UchiPro\Courses\CourseFeatures;
use UchiPro\Identity;

class CoursesTest extends TestCase
{
    /**
     * @var Identity
     */
    private $identity;

    public function setUp(): void
    {
        $url = getenv('UCHIPRO_URL');
        $login = getenv('UCHIPRO_LOGIN');
        $password = getenv('UCHIPRO_PASSWORD');
        $accessToken = getenv('UCHIPRO_ACCESS_TOKEN');

        $this->identity = !empty($accessToken)
          ? Identity::createByAccessToken($url, $accessToken)
          : Identity::createByLogin($url, $login, $password);
    }

    private function getApiClient(): ApiClient
    {
        return ApiClient::create($this->identity);
    }

    public function testCreateCourse(): void
    {
        $courseId = 'b48a26bf-6096-4245-9591-2900d8b0cd02';
        $courseTitle = 'Тестовый курс';

        $course1 = $this->getApiClient()->courses()->createCourse();
        $this->assertInstanceOf(Course::class, $course1);

        $course2 = $this->getApiClient()->courses()->createCourse($courseId);
        $this->assertInstanceOf(Course::class, $course2);
        $this->assertSame($courseId, $course2->id);

        $course3 = $this->getApiClient()->courses()->createCourse(null, $courseTitle);
        $this->assertInstanceOf(Course::class, $course3);
        $this->assertSame($courseTitle, $course3->title);

        $course4 = $this->getApiClient()->courses()->createCourse($courseId, $courseTitle);
        $this->assertInstanceOf(Course::class, $course4);
        $this->assertSame($courseId, $course4->id);
        $this->assertSame($courseTitle, $course4->title);
    }

    public function testFindCourses(): void
    {
        $coursesApi = $this->getApiClient()->courses();
        $coursesCriteria = $coursesApi->createCriteria();
        $courses = $coursesApi->findBy($coursesCriteria);

        $this->assertTrue(is_array($courses));

        $courseTypeIdCorrect = true;
        foreach ($courses as $course) {
            if (!empty($course->type->title) && empty($course->type->id)) {
                $courseTypeIdCorrect = false;
            }
        }
        $this->assertTrue($courseTypeIdCorrect);

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

    public function testFindCourseById(): void
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

        $notExistsCourse = $coursesApi->findById("{$course->id}1");
        $this->assertEmpty($notExistsCourse);
    }

    public function testGetCourseFeatures(): void
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
