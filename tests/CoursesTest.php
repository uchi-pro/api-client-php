<?php

declare(strict_types=1);

namespace UchiPro\Tests;

use UchiPro\Courses\Course;
use UchiPro\Courses\CourseFeatures;
use UchiPro\Courses\LessonType;
use UchiPro\Courses\Tag;

class CoursesTest extends TestCase
{
    public function testnewCourse(): void
    {
        $courseId = 'b48a26bf-6096-4245-9591-2900d8b0cd02';
        $courseTitle = 'Тестовый курс';

        $course1 = $this->getApiClient()->courses()->newCourse();
        $this->assertInstanceOf(Course::class, $course1);

        $course2 = $this->getApiClient()->courses()->newCourse($courseId);
        $this->assertInstanceOf(Course::class, $course2);
        $this->assertSame($courseId, $course2->id);

        $course3 = $this->getApiClient()->courses()->newCourse(null, $courseTitle);
        $this->assertInstanceOf(Course::class, $course3);
        $this->assertSame($courseTitle, $course3->title);

        $course4 = $this->getApiClient()->courses()->newCourse($courseId, $courseTitle);
        $this->assertInstanceOf(Course::class, $course4);
        $this->assertSame($courseId, $course4->id);
        $this->assertSame($courseTitle, $course4->title);
    }

    public function testFindCourses(): void
    {
        $coursesApi = $this->getApiClient()->courses();
        $courses = $coursesApi->findBy();

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
        $this->assertTrue($hours > 0, 'Курсы с часами не найдены.');
    }

    public function testFindInactiveCourses(): void
    {
        $coursesApi = $this->getApiClient()->courses();
        $coursesCriteria = $coursesApi->newCriteria();
        $coursesCriteria->withInactive = true;
        $courses = $coursesApi->findBy($coursesCriteria);

        $inactiveCoursesCount = 0;
        foreach ($courses as $course) {
            if ($course->isActive === false) {
                $inactiveCoursesCount++;
            }
        }

        $this->assertGreaterThan(0, $inactiveCoursesCount);
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

    public function testFindCourseWithTags(): void
    {
        $coursesApi = $this->getApiClient()->courses();

        $tag = $coursesApi->newTag('ac88d113-286b-42a4-9306-131ed982f505'); // Общая часть курса 46.Б
        $criteria = $coursesApi->newCriteria()->withTags([$tag]);
        $foundCourses = $coursesApi->findBy($criteria);

        if (empty($foundCourses[0])) {
            $this->markTestSkipped('Курсы с тегами не найдены.');
        }
        $foundCourse = $foundCourses[0];

        $this->assertNotEmpty($foundCourse->tags[0]->id);
        $this->assertNotEmpty($foundCourse->tags[0]->title);
    }

    public function testGetCourseFeatures(): void
    {
        $coursesApi = $this->getApiClient()->courses();
        $courses = $coursesApi->findBy();

        if (empty($courses[0])) {
            $this->markTestSkipped('Курс для теста не найден.');
        }
        $course = $courses[0];

        $courseFeatures = $coursesApi->getCourseFeatures($course);
        $this->assertInstanceOf(CourseFeatures::class, $courseFeatures);
    }

    public function testGetTagsTree()
    {
        $tagsTree = $this->getApiClient()->courses()->getTagsTree();
        $this->assertNotEmpty($tagsTree);
        $this->assertNotEmpty($tagsTree[0]->children);
        $this->assertInstanceOf(Tag::class, $tagsTree[0]->children[0]);
        $this->assertEquals($tagsTree[0]->id, $tagsTree[0]->children[0]->parentId);
    }

    public function testSaveCourse()
    {
        $coursesApi = $this->getApiClient()->courses();

        $course = $coursesApi->newCourse();
        $course->title = sprintf('Тестовый курс %s', date('YmdHis'));

        $savedCourse = $coursesApi->saveCourse($course);

        $lesson = $coursesApi->newLesson();
        $lesson->title = 'Первая лекция';
        $lesson->type = LessonType::createLecture();
        $coursesApi->saveLesson($savedCourse, $lesson);

        $lesson = $coursesApi->newLesson();
        $lesson->title = 'Вторая лекция';
        $lesson->type = LessonType::createLecture();
        $coursesApi->saveLesson($savedCourse, $lesson);

        $foundCourse = $coursesApi->findById($savedCourse->id);

        $this->assertEquals($course->title, $foundCourse->title);
        $this->assertEquals(2, $foundCourse->lessonsCount);
    }
}
