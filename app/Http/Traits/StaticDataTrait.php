<?php

namespace App\Http\Traits;

trait StaticDataTrait
{
    use CommonTrait, ApiHelperTrait;

    protected $resourceMappings = [
        'file_url' => 'resource_url',
        'file_type' => 'resource_type',
        'file_name' => 'resource_name',
        'file_size' => 'resource_size'
    ];

    public function mapResourceData($resource)
    {
        $mappedResource = [];
        foreach ($this->resourceMappings as $key => $value) {
            $mappedResource[$value] = $resource[$key] ?? $resource[$value] ?? null;
        }
        return $mappedResource;
    }

    public function processResource($resource, $folder_name)
    {
        if (isset($resource['resource_url']) && $resource['resource_url'] && $resource['resource_type'] !== 'url' && $resource['resource_type'] !== 'youtube') {
            $validExtensions = $this->getValidExtensions($resource['resource_type']);
            $extension = strtolower($resource['resource_url']->getClientOriginalExtension());

            if (!empty($validExtensions) && !in_array($extension, $validExtensions)) {
                return [
                    'error' => true,
                    'message' => 'Jenis file tidak valid.',
                    'path' => null,
                    'extension' => null,
                    'size' => null,
                ];
            }

            $folder = $this->getUploadFolder($resource['resource_type'], $folder_name);
            $uploadResult = $this->uploadFile($resource['resource_url'], $folder);

            return array_merge($uploadResult, ['error' => false, 'message' => null]);
        }

        if (isset($resource['resource_url']) && ($resource['resource_type'] === 'url' || $resource['resource_type'] === 'youtube')) {
            return [
                'path' => $resource['resource_url'],
                'size' => null,
                'extension' => null,
                'error' => false,
                'message' => null,
            ];
        }

        return [
            'error' => true,
            'message' => 'Resource tidak valid.',
            'path' => null,
            'extension' => null,
            'size' => null,
        ];
    }

    public function getValidExtensions($type)
    {
        return match ($type) {
            'image' => ['png', 'jpeg', 'jpg'],
            'document' => ['doc', 'docx', 'pdf'],
            'archive' => ['rar', 'zip'],
            'audio' => ['mp3', 'wav', 'mpeg'],
            'video' => ['mp4', 'mkv', 'mpeg'],
            default => [],
        };
    }

    public function getUploadFolder($type, $folder_name)
    {
        return match ($type) {
            'image' => $folder_name . '/images',
            'document' => $folder_name . '/documents',
            'archive' => $folder_name . '/archives',
            'audio' => $folder_name . '/audio',
            'video' => $folder_name . '/videos',
            default => $folder_name . '/others',
        };
    }

    public function generateQuestionTypes()
    {
        return ['Essay', 'Pilihan Ganda', 'Pilihan Ganda Complex', 'True False'];
    }

    public function generateReligions()
    {
        return ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Budha', 'Konghucu'];
    }

    private function convertSubClassId($sub_class_id)
    {
        return preg_replace('/-(\d+)$/', '/$1', $sub_class_id);
    }

    private function normalizeId($id)
    {
        return str_replace('/', '-', $id);
    }

    private function transformCourse($course_data, $course_id)
    {
        foreach ($course_data as $course) {
            if ($course->id === $course_id) {
                return [
                    'id' => $course->id,
                    'name' => $course->courses_title
                ];
            }
        }
        return null;
    }

    private function transformLevel($levels, $level_id)
    {
        foreach ($levels as $level) {
            if ($level->id === $level_id) {
                return [
                    'id' => $level->id,
                    'name' => $level->name
                ];
            }
        }
        return null;
    }

    private function transformQuestionWithSection($sections, $section_id)
    {
        foreach ($sections as $section) {
            if ($section->id === $section_id) {
                return [
                    'id' => $section->id,
                    'name' => $section->name
                ];
            }
        }
        return null;
    }

    private function transformAcademicYear($academic_years, $academic_year_id)
    {
        foreach ($academic_years as $academic_year) {
            if ($academic_year->id === $academic_year_id) {
                return [
                    'id' => $academic_year->id,
                    'name' => $academic_year->year
                ];
            }
        }
        return null;
    }

    private function formatTimeAllocation($time_allocation)
    {
        if (strpos($time_allocation, ':') !== false) {
            $time_parts = explode(':', $time_allocation);
            if (count($time_parts) === 3) {
                return $time_parts[0] . ':' . $time_parts[1];
            }
        }
        return $time_allocation;
    }


    private function authorizeTeacher()
    {
        if (!$this->isAuthorized('TEACHER')) {
            return redirect('/login/teacher');
        }
    }

    private function authorizeStudent(){
        if (!$this->isAuthorized('STUDENT')) {
            return redirect('/login');
        }
    }

    private function authorizeTeacherPengelola()
    {
        if (!$this->isAuthorizedTeacherPengelola('PENGELOLA')) {
            return redirect('/login');
        }
    }

    private function authorizeTeacherPenilai()
    {
        if (!$this->isAuthorizedTeacherPenilai('PENILAI')) {
            return redirect('/login');
        }
    }

    private function authorizeStaff()
    {
        if (!$this->isAuthorized('STAFF')) {
            return redirect('/login');
        }
    }
}