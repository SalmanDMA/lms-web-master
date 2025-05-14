<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\Choices;
use App\Models\ClassExam;
use App\Models\EnrollmentExam;
use App\Models\ExamSection;
use App\Models\ExamTeacher;
use App\Models\Learning;
use App\Models\Question;
use App\Models\QuestionAttachment;
use App\Models\QuestionCategory;
use App\Models\Response;
use App\Models\Staff;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    use CommonTrait;

    public function index_student_exam($id)
    {
        $userLogin = auth()->user();

        $isClassExam = Question::where('exam_id', $id)->exists();
        $isSchoolExam = Question::where('school_exam_id', $id)->exists();

        if (!$isClassExam && !$isSchoolExam) {
            return $this->sendError('Ujian tidak ditemukan.', null, 200);
        }

        $query = $isClassExam ? Question::where('exam_id', $id) : Question::where('school_exam_id', $id);

        $validExamIds = $isClassExam
            ? Response::where('student_id', $userLogin->is_student->id)->pluck('exam_id')->toArray()
            : EnrollmentExam::where('student_id', $userLogin->is_student->id)->pluck('exam_id')->toArray();

        if (!in_array($id, $validExamIds)) {
            return $this->sendError('Anda tidak memiliki akses ke ujian ini.', null, 200);
        }

        $questions = $query->orderBy('created_at', 'desc')->get();

        if ($questions->isEmpty()) {
            return $this->sendError('Pertanyaan tidak ditemukan.', null, 200);
        }

        $questions->load(['class_exam', 'school_exam', 'exam_sections', 'question_category', 'choices', 'question_attachments', 'answers']);

        foreach ($questions as $question) {
            if ($question->section_id) {
                $section = ExamSection::find($question->section_id);
                $question->section_id = $section;
            } else {
                $question->section_id = $question->section_id;
            }

            if ($question->category_id) {
                $category = QuestionCategory::find($question->category_id);
                $question->category_id = $category;
            } else {
                $question->category_id = $question->category_id;
            }
        }

        return $this->sendResponse($questions, 'Berhasil mengambil semua data pertanyaan.');
    }

    public function index_teacher_by_school_exam(Request $request)
    {
        $userLogin = auth()->user();
        $search = $request->query('search');
        $limit = $request->query('limit');

        $query = Question::query();

        $teacherSchoolExamIds = ExamTeacher::where('teacher_id', $userLogin->is_teacher->id)->pluck('exam_id')->toArray();

        if (!empty($teacherSchoolExamIds)) {
            $query->whereIn('school_exam_id', $teacherSchoolExamIds);
        }

        if ($search) {
            $query->where('question_text', 'like', '%' . $search . '%');
        }

        $query->orderBy('created_at', 'desc');

        $questions = $limit ? $query->limit($limit)->get() : $query->get();

        if ($questions->isEmpty()) {
            return $this->sendError('Pertanyaan tidak ditemukan.', null, 200);
        }

        $questions->load(['class_exam', 'school_exam', 'exam_sections', 'question_category', 'choices', 'question_attachments', 'answers']);

        return $this->sendResponse($questions, 'Berhasil mengambil data pertanyaan berdasarkan school_exam_id.');
    }

    public function index_teacher_by_class_exam(Request $request)
    {
        $userLogin = auth()->user();
        $search = $request->query('search');
        $limit = $request->query('limit');

        $query = Question::query();

        $learningIds = Learning::where('teacher_id', $userLogin->is_teacher->id)->pluck('id');
        $validExamIds = ClassExam::whereIn('learning_id', $learningIds)->pluck('id')->toArray();

        if (!empty($validExamIds)) {
            $query->whereIn('exam_id', $validExamIds);
        }

        if ($search) {
            $query->where('question_text', 'like', '%' . $search . '%');
        }

        $query->orderBy('created_at', 'desc');

        $questions = $limit ? $query->limit($limit)->get() : $query->get();

        if ($questions->isEmpty()) {
            return $this->sendError('Pertanyaan tidak ditemukan.', null, 200);
        }

        $questions->load(['class_exam', 'school_exam', 'exam_sections', 'question_category', 'choices', 'question_attachments', 'answers']);

        return $this->sendResponse($questions, 'Berhasil mengambil data pertanyaan berdasarkan exam_id.');
    }

    public function index_admin(Request $request)
    {
        $search = $request->query('search');
        $limit = $request->query('limit');

        $query = Question::query();

        if ($search) {
            $query->where('question_text', 'like', '%' . $search . '%');
        }

        $query->orderBy('created_at', 'desc');

        if ($limit) {
            $questions = $query->limit($limit)->get();
        } else {
            $questions = $query->get();
        }

        if ($questions->isEmpty()) {
            return $this->sendError('Pertanyaan tidak ditemukan.', null, 200);
        }

        $questions->load(['class_exam', 'school_exam', 'exam_sections', 'question_category', 'choices', 'question_attachments', 'answers']);

        return $this->sendResponse($questions, 'Berhasil mengambil semua data pertanyaan.');
    }

    public function show($id)
    {
        $question = Question::find($id);

        if (!$question) {
            return $this->sendError('Pertanyaan tidak ditemukan.', null, 200);
        }

        $question->load(['class_exam', 'school_exam', 'exam_sections', 'question_category', 'choices', 'question_attachments', 'answers']);
        return $this->sendResponse($question, 'Berhasil mengambil data question');
    }

    public function store(Request $request)
    {
        $userLogin = auth()->user();

        $data = $request->all();

        $validator = Validator::make($data, [
            'exam_id' => 'nullable|exists:class_exams,id',
            'school_exam_id' => 'nullable|exists:school_exams,id',
            'section_id' => 'nullable|exists:exam_sections,id',
            'category_id' => 'required|exists:question_categories,id',
            'question_text' => 'required|string|max:255',
            'question_type' => 'required|in:Essay,Pilihan Ganda,Pilihan Ganda Complex,True False',
            'point' => 'required|integer',
            'grade_method' => 'nullable|string|max:255',
            'difficult' => 'required|string|max:255',
            'choices' => 'nullable|array',
            'choices.*.choice_text' => 'nullable|string|max:255',
            'choices.*.is_true' => 'nullable|boolean',
            'resources.*.file_name' => 'nullable|string',
            'resources.*.file_url' => 'nullable',
            'resources.*.file_type' => 'nullable|string',
        ], [
            'category_id.required' => 'Ups, Anda Belum Melengkapi Form',
            'question_text.required' => 'Ups, Anda Belum Melengkapi Form',
            'question_type.required' => 'Ups, Anda Belum Melengkapi Form',
            'point.required' => 'Ups, Anda Belum Melengkapi Form',
            'difficult.required' => 'Ups, Anda Belum Melengkapi Form',
            'exam_id.exists' => 'Ups, Id Exam Tidak Ada',
            'category_id.exists' => 'Ups, Id Category Tidak Ada',
            'question_type.in' => 'Ups, Tipe Soal Tidak Valid'
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    if ($resource['file_url'] instanceof UploadedFile) {
                        $validExtensions = [];

                        switch ($resource['file_type']) {
                            case 'image':
                                $validExtensions = ['png', 'jpeg', 'jpg'];
                                break;
                            case 'document':
                                $validExtensions = ['doc', 'docx', 'pdf'];
                                break;
                            case 'archive':
                                $validExtensions = ['rar', 'zip'];
                                break;
                            case 'audio':
                                $validExtensions = ['mp3', 'wav', 'mpeg'];
                                break;
                            case 'video':
                                $validExtensions = ['mp4', 'mkv', 'mpeg'];
                                break;
                            default:
                                $validExtensions = [];
                                break;
                        }

                        if (!empty($validExtensions) && $resource['file_type'] !== 'url' && $resource['file_type'] !== 'youtube') {
                            $extension = strtolower($resource['file_url']->getClientOriginalExtension());
                            if (!in_array($extension, $validExtensions)) {
                                return $this->sendError('Jenis file tidak valid.', null, 200);
                            }
                        }
                    }
                }
            }
        }

        if ($userLogin->role === 'TEACHER') {
            $teacherExamIds = ExamTeacher::where('teacher_id', $userLogin->is_teacher->id)->pluck('exam_id')->toArray();

            if (empty($teacherExamIds)) {
                $learningIds = Learning::where('teacher_id', $userLogin->is_teacher->id)->pluck('id')->toArray();
                $validExamIds = ClassExam::whereIn('learning_id', $learningIds)->pluck('id')->toArray();

                if (isset($data['exam_id']) && !in_array($data['exam_id'], $validExamIds)) {
                    return $this->sendError('Anda tidak mempunyai akses untuk mengakses exam ini.', null, 200);
                }
            } else {
                if (isset($data['school_exam_id']) && !in_array($data['school_exam_id'], $teacherExamIds)) {
                    return $this->sendError('Anda tidak mempunyai akses untuk mengakses exam ini.', null, 200);
                }
            }
        }

        if ($userLogin->role === 'STAFF') {
            $staffCurriculum = Staff::where('user_id', $userLogin->id)->first();

            if (!$staffCurriculum || $staffCurriculum->authority != "KURIKULUM") {
                return $this->sendError('Anda tidak mempunyai akses untuk mengakses curriculum ini.', null, 200);
            }
        }

        $generateIdQuestion = IdGenerator::generate(['table' => 'questions', 'length' => 16, 'prefix' => 'QU-']);

        $question = Question::create([
            'id' => $generateIdQuestion,
            'exam_id' => $data['exam_id'] ?? null,
            'school_exam_id' => $data['school_exam_id'] ?? null,
            'section_id' => $data['section_id'] ?? null,
            'category_id' => $data['category_id'],
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'],
            'point' => $data['point'],
            'grade_method' => $data['grade_method'],
            'difficult' => $data['difficult'],
        ]);

        if ($data['question_type'] !== 'Essay') {
            foreach ($data['choices'] as $choiceData) {
                $generateIdChoice = IdGenerator::generate(['table' => 'choices', 'length' => 16, 'prefix' => 'CH-']);

                Choices::create([
                    'id' => $generateIdChoice,
                    'question_id' => $question->id,
                    'choice_text' => $choiceData['choice_text'],
                    'is_true' => $choiceData['is_true'],
                ]);
            }
        }

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    $uploadResult = null;

                    if ($resource['file_url'] instanceof UploadedFile) {
                        $folder = match ($resource['file_type']) {
                            'image' => 'questions/images',
                            'document' => 'questions/documents',
                            'archive' => 'questions/archives',
                            'audio' => 'questions/audio',
                            'video' => 'questions/videos',
                            default => 'questions/others',
                        };

                        $uploadResult = $this->uploadFile($resource['file_url'], $folder);
                    } else {
                        $uploadResult['path'] = $resource['file_url'];
                        $uploadResult['size'] = $resource['file_size'] ?? null;
                        $uploadResult['extension'] = $resource['file_extension'] ?? null;
                    }

                    $generateIdQuestionAttachment = IdGenerator::generate(['table' => 'question_attachments', 'length' => 16, 'prefix' => 'QUA-']);

                    QuestionAttachment::create([
                        'id' => $generateIdQuestionAttachment,
                        'question_id' => $question->id,
                        'file_name' => $resource['file_name'],
                        'file_type' => $resource['file_type'],
                        'file_url' => $uploadResult['path'],
                        'file_size' => $uploadResult['size'],
                        'file_extension' => $uploadResult['extension'],
                    ]);
                }
            }
        }

        $question->load(['class_exam', 'school_exam', 'exam_sections', 'question_category', 'choices', 'question_attachments', 'answers']);

        return $this->sendResponse($question, 'Berhasil menambahkan data question', 201);
    }


    public function update(Request $request, $id)
    {
        $question = Question::find($id);

        if (!$question) {
            return $this->sendError('Question tidak ditemukan.', null, 200);
        }

        $validator = Validator::make($request->all(), [
            'exam_id' => 'nullable|exists:class_exams,id',
            'school_exam_id' => 'nullable|exists:school_exams,id',
            'section_id' => 'nullable|exists:exam_sections,id',
            'category_id' => 'required|exists:question_categories,id',
            'question_text' => 'required|string|max:255',
            'question_type' => 'required|in:Essay,Pilihan Ganda,Pilihan Ganda Complex,True False',
            'point' => 'required|integer',
            'grade_method' => 'nullable|string|max:255',
            'difficult' => 'required|string|max:255',
            'choices' => 'nullable|array',
            'choices.*.choice_text' => 'nullable|string|max:255',
            'choices.*.is_true' => 'nullable|boolean',
            'resources.*.file_name' => 'nullable|string',
            'resources.*.file_url' => 'nullable',
            'resources.*.file_type' => 'nullable|string',
        ], [
            'category_id.required' => 'Ups, Anda Belum Melengkapi Form',
            'question_text.required' => 'Ups, Anda Belum Melengkapi Form',
            'question_type.required' => 'Ups, Anda Belum Melengkapi Form',
            'point.required' => 'Ups, Anda Belum Melengkapi Form',
            'difficult.required' => 'Ups, Anda Belum Melengkapi Form',
            'exam_id.exists' => 'Ups, Id Exam Tidak Ada',
            'category_id.exists' => 'Ups, Id Category Tidak Ada',
            'question_type.in' => 'Ups, Tipe Soal Tidak Valid'
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        $data = $request->all();

        $question->update([
            'exam_id' => $data['exam_id'] ?? $question->exam_id,
            'school_exam_id' => $data['school_exam_id'] ?? $question->school_exam_id,
            'section_id' => $data['section_id'] ?? $question->section_id,
            'category_id' => $data['category_id'] ?? $question->category_id,
            'question_text' => $data['question_text'] ?? $question->question_text,
            'question_type' => $data['question_type'] ?? $question->question_type,
            'point' => $data['point'] ?? $question->point,
            'grade_method' => $data['grade_method'] ?? $question->grade_method,
            'difficult' => $data['difficult'] ?? $question->difficult,
        ]);

        if ($data['question_type'] === 'Essay') {
            Choices::where('question_id', $question->id)->delete();
        } else {
            $existingChoices = Choices::where('question_id', $question->id)->get();

            $existingChoiceIds = $existingChoices->pluck('id')->toArray();

            foreach ($data['choices'] as $choiceData) {
                if (isset($choiceData['choice_text'])) {
                    $existingChoice = $existingChoices->firstWhere('choice_text', $choiceData['choice_text']);

                    if ($existingChoice) {
                        $existingChoice->update([
                            'is_true' => $choiceData['is_true'],
                        ]);

                        $existingChoiceIds = array_diff($existingChoiceIds, [$existingChoice->id]);
                    } else {
                        $generateIdChoice = IdGenerator::generate(['table' => 'choices', 'length' => 16, 'prefix' => 'CH-']);

                        Choices::create([
                            'id' => $generateIdChoice,
                            'question_id' => $question->id,
                            'choice_text' => $choiceData['choice_text'],
                            'is_true' => $choiceData['is_true'],
                        ]);
                    }
                }
            }

            if (!empty($existingChoiceIds)) {
                Choices::whereIn('id', $existingChoiceIds)->delete();
            }
        }

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    $existingResources = $question->question_attachments()->where('file_type', $resource['file_type'])->get();
                    foreach ($existingResources as $existingResource) {
                        $this->removeFile($existingResource->file_url);
                        $existingResource->delete();
                    }

                    $uploadResult = $this->handleFileUpload($resource);
                    $generateIdQuestionAttachment = IdGenerator::generate(['table' => 'question_attachments', 'length' => 16, 'prefix' => 'QUA-']);

                    QuestionAttachment::create([
                        'id' => $generateIdQuestionAttachment,
                        'question_id' => $question->id,
                        'file_name' => $resource['file_name'],
                        'file_type' => $resource['file_type'],
                        'file_url' => $uploadResult['path'],
                        'file_size' => $uploadResult['size'],
                        'file_extension' => $uploadResult['extension'],
                    ]);
                }
            }
        }

        $question->load(['class_exam', 'school_exam', 'exam_sections', 'question_category', 'choices', 'question_attachments', 'answers']);
        return $this->sendResponse($question, 'Question updated successfully');
    }

    public function destroy($id)
    {
        $question = Question::find($id);

        if (!$question) {
            return $this->sendError('Question tidak ditemukan.', null, 200);
        }

        foreach ($question->question_attachments as $resource) {
            $this->removeFile($resource->file_url);
            $resource->delete();
        }

        $question->delete();

        return $this->sendResponse($question, 'Berhasil menghapus data question');
    }
}
