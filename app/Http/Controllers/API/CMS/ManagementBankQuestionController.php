<?php

namespace App\Http\Controllers\API\CMS;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\BankQuestion;
use App\Models\ChoicesQuestionBank;
use App\Models\QuestionAttachmentBank;
use Carbon\Carbon;
use Haruncpi\LaravelIdGenerator\IdGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ManagementBankQuestionController extends Controller
{
    use CommonTrait;

    public function index(Request $request)
    {
        $userLogin = auth()->user();
        $search = $request->query('search');

        $query = BankQuestion::query();

        if ($userLogin->role === 'TEACHER') {
            $query->where('teacher_id', $userLogin->is_teacher->id);
        } else {
            $query->where('status', 'sent');
        }

        if ($search) {
            $query->where('question_text', 'like', '%' . $search . '%');
        }

        $questions = $query->get();

        if ($questions->isEmpty()) {
            return $this->sendError('Question tidak ditemukan.', null, 200);
        }

        $questions->load(['question_attachment_banks', 'teacher', 'question_category', 'choices_banks']);

        return $this->sendResponse($questions, 'Berhasil mengambil semua data question');
    }


    public function store(Request $request)
    {
        $userLogin = auth()->user();

        // Validasi utama
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:question_categories,id',
            'question_text' => 'required|string|max:255',
            'question_type' => 'required|in:Essay,Pilihan Ganda,Pilihan Ganda Complex,True False',
            'point' => 'required|integer',
            'grade_method' => 'nullable|string|max:255',
            'course' => 'required|string|max:255',
            'class_level' => 'required|string|max:255',
            'is_required' => 'nullable|boolean',
            'shared_at' => 'required|string|max:255',
            'shared_count' => 'required|integer',
            'status' => 'nullable|string|max:255',
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
            'course.required' => 'Ups, Anda Belum Melengkapi Form',
            'class_level.required' => 'Ups, Anda Belum Melengkapi Form',
            'shared_at.required' => 'Ups, Anda Belum Melengkapi Form',
            'shared_count.required' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.exists' => 'Ups, Id Teacher Tidak Ada',
            'category_id.exists' => 'Ups, Id Category Tidak Ada',
            'question_type.in' => 'Ups, Tipe Soal Tidak Valid',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
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


        $data = $request->all();

        // Validasi tambahan untuk admin
        if ($userLogin->role !== 'TEACHER') {
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
            ], [
                'teacher_id.required' => 'Ups, Anda Belum Melengkapi Form',
            ]);

            if ($validator->fails()) {
                return $this->failsValidate($validator->errors());
            }

            $data['teacher_id'] = $data['teacher_id'];
            $data['status'] = 'sent';
        } else {
            $data['teacher_id'] = $userLogin->is_teacher->id;
            $data['status'] = 'pending';
        }

        // Generate ID untuk bank soal
        $generateIdBankQuestion = IdGenerator::generate(['table' => 'bank_questions', 'length' => 16, 'prefix' => 'BQ-']);

        // Buat entri bank soal baru
        $question = BankQuestion::create([
            'id' => $generateIdBankQuestion,
            'teacher_id' => $data['teacher_id'],
            'category_id' => $data['category_id'],
            'question_text' => $data['question_text'],
            'question_type' => $data['question_type'],
            'point' => $data['point'],
            'grade_method' => $data['grade_method'],
            'course' => $data['course'],
            'class_level' => $data['class_level'],
            'is_required' => $data['is_required'] ?? false,
            'shared_at' => $data['shared_at'],
            'shared_count' => $data['shared_count'],
            'status' => $data['status'],
        ]);

        // Tambah pilihan untuk jenis soal yang memerlukan pilihan
        if ($data['question_type'] !== 'Essay') {
            foreach ($data['choices'] as $choiceData) {
                $generateIdChoiceQuestionBank = IdGenerator::generate(['table' => 'choices_question_banks', 'length' => 16, 'prefix' => 'CQB-']);

                ChoicesQuestionBank::create([
                    'id' => $generateIdChoiceQuestionBank,
                    'question_id' => $question->id,
                    'choice_text' => $choiceData['choice_text'],
                    'is_true' => $choiceData['is_true'],
                ]);
            }
        }

        // Tambah attachment jika ada
        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    $uploadResult = null;

                    if ($resource['file_type'] === 'url' || $resource['file_type'] === 'youtube') {
                        $uploadResult['path'] = $resource['file_url'];
                        $uploadResult['size'] = null;
                        $uploadResult['extension'] = null;
                    } else {
                        $folder = match ($resource['file_type']) {
                            'image' => 'questions/images',
                            'document' => 'questions/documents',
                            'archive' => 'questions/archives',
                            'audio' => 'questions/audio',
                            'video' => 'questions/videos',
                            default => 'questions/others',
                        };

                        $uploadResult = $this->uploadFile($resource['file_url'], $folder);
                    }

                    $generateIdQuestionAttachment = IdGenerator::generate(['table' => 'question_attachment_banks', 'length' => 16, 'prefix' => 'QAB-']);

                    QuestionAttachmentBank::create([
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

        // Load attachment bank question
        $question->load(['question_attachment_banks', 'teacher', 'question_category', 'choices_banks']);

        return $this->sendResponse($question, 'Berhasil menambahkan data question', 201);
    }

    public function show($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $question = BankQuestion::where('teacher_id', $userLogin->is_teacher->id)->find($id);
        } else {
            $question = BankQuestion::where('status', 'sent')->find($id);
        }

        if (!$question) {
            return $this->sendError('Question tidak ditemukan.', null, 200);
        }

        $question->load(['question_attachment_banks', 'teacher', 'question_category', 'choices_banks']);
        return $this->sendResponse($question, 'Berhasil mengambil data question');
    }

    public function update(Request $request, $id)
    {
        $userLogin = auth()->user();

        // Menemukan soal berdasarkan peran pengguna
        if ($userLogin->role === 'TEACHER') {
            $question = BankQuestion::where('teacher_id', $userLogin->is_teacher->id)->find($id);
        } else {
            $question = BankQuestion::where('status', 'sent')->find($id);
        }

        // Cek jika soal tidak ditemukan
        if (!$question) {
            return $this->sendError('Question tidak ditemukan.', null, 200);
        }

        // Validasi utama
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:question_categories,id',
            'question_text' => 'required|string|max:255',
            'question_type' => 'required|in:Essay,Pilihan Ganda,Pilihan Ganda Complex,True False',
            'point' => 'required|integer',
            'grade_method' => 'nullable|string|max:255',
            'course' => 'required|string|max:255',
            'class_level' => 'required|string|max:255',
            'is_required' => 'nullable|boolean',
            'shared_at' => 'required|string|max:255',
            'shared_count' => 'required|integer',
            'status' => 'nullable|string|max:255',
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
            'course.required' => 'Ups, Anda Belum Melengkapi Form',
            'class_level.required' => 'Ups, Anda Belum Melengkapi Form',
            'shared_at.required' => 'Ups, Anda Belum Melengkapi Form',
            'shared_count.required' => 'Ups, Anda Belum Melengkapi Form',
            'teacher_id.exists' => 'Ups, Id Teacher Tidak Ada',
            'category_id.exists' => 'Ups, Id Category Tidak Ada',
            'question_type.in' => 'Ups, Tipe Soal Tidak Valid',
        ]);

        if ($validator->fails()) {
            return $this->failsValidate($validator->errors());
        }

        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
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

        $data = $request->all();

        if ($userLogin->role !== 'TEACHER') {
            $validator = Validator::make($request->all(), [
                'teacher_id' => 'required|exists:teachers,id',
            ], [
                'teacher_id.required' => 'Ups, Anda Belum Melengkapi Form',
            ]);

            if ($validator->fails()) {
                return $this->failsValidate($validator->errors());
            }

            $data['teacher_id'] = $data['teacher_id'];
            $data['status'] = 'sent';
        } else {
            $data['status'] = 'pending';
            $data['teacher_id'] = $userLogin->is_teacher->id;
        }

        // Update bank soal
        $question->update([
            'category_id' => $data['category_id'] ?? $question->category_id,
            'question_text' => $data['question_text'] ?? $question->question_text,
            'question_type' => $data['question_type'] ?? $question->question_type,
            'point' => $data['point'] ?? $question->point,
            'grade_method' => $data['grade_method'] ?? $question->grade_method,
            'course' => $data['course'] ?? $question->course,
            'class_level' => $data['class_level'] ?? $question->class_level,
            'is_required' => $data['is_required'] ?? $question->is_required,
            'shared_at' => $data['shared_at'] ?? $question->shared_at,
            'shared_count' => $data['shared_count'] ?? $question->shared_count,
            'status' => $data['status'] ?? $question->status,
        ]);

        // Penanganan perubahan jenis soal
        if ($data['question_type'] === 'Essay') {
            ChoicesQuestionBank::where('question_id', $question->id)->delete();
        } else {
            $existingChoices = ChoicesQuestionBank::where('question_id', $question->id)->get();
            $existingChoiceTexts = $existingChoices->pluck('choice_text')->toArray();

            // Perbarui atau tambahkan pilihan
            foreach ($data['choices'] as $choiceData) {
                // Periksa apakah pilihan sudah ada berdasarkan text
                $existingChoice = $existingChoices->firstWhere('choice_text', $choiceData['choice_text']);

                if ($existingChoice) {
                    // Update existing choice
                    $existingChoice->update([
                        'is_true' => $choiceData['is_true'],
                    ]);
                } else {
                    // Tambah choice baru
                    $generateIdChoiceQuestionBank = IdGenerator::generate(['table' => 'choices_question_banks', 'length' => 16, 'prefix' => 'CQB-']);
                    ChoicesQuestionBank::create([
                        'id' => $generateIdChoiceQuestionBank,
                        'question_id' => $question->id,
                        'choice_text' => $choiceData['choice_text'],
                        'is_true' => $choiceData['is_true'],
                    ]);
                }
            }

            // Hapus pilihan yang tidak ada dalam data baru
            $newChoiceTexts = array_column($data['choices'], 'choice_text');
            $choicesToDelete = $existingChoices->whereNotIn('choice_text', $newChoiceTexts);
            foreach ($choicesToDelete as $choice) {
                $choice->delete();
            }
        }

        // Penanganan lampiran
        if (isset($data['resources'])) {
            foreach ($data['resources'] as $resource) {
                if (isset($resource['file_url'])) {
                    $existingResources = $question->question_attachment_banks()->where('file_type', $resource['file_type'])->get();

                    foreach ($existingResources as $existingResource) {
                        $this->removeFile($existingResource->file_url);
                        $existingResource->delete();
                    }

                    foreach ($data['resources'] as $resource) {
                        if (isset($resource['file_url'])) {
                            $uploadResult = null;

                            if ($resource['file_type'] === 'url' || $resource['file_type'] === 'youtube') {
                                $uploadResult['path'] = $resource['file_url'];
                                $uploadResult['size'] = null;
                                $uploadResult['extension'] = null;
                            } else {
                                $folder = match ($resource['file_type']) {
                                    'image' => 'questions/images',
                                    'document' => 'questions/documents',
                                    'archive' => 'questions/archives',
                                    'audio' => 'questions/audio',
                                    'video' => 'questions/videos',
                                    default => 'questions/others',
                                };

                                $uploadResult = $this->uploadFile($resource['file_url'], $folder);
                            }

                            $uploadResult = $this->uploadFile($resource['file_url'], $folder);
                            $generateIdQuestionAttachment = IdGenerator::generate(['table' => 'question_attachment_banks', 'length' => 16, 'prefix' => 'QAB-']);
                            QuestionAttachmentBank::create([
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
            }
        }

        $question->load(['question_attachment_banks', 'teacher', 'question_category', 'choices_banks']);
        return $this->sendResponse($question, 'Berhasil mengubah data question');
    }

    public function update_status($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $question = BankQuestion::where('teacher_id', $userLogin->is_teacher->id)->find($id);
        } else {
            $question = BankQuestion::find($id);
        }

        if (!$question) {
            return $this->sendError('Question tidak ditemukan.', null, 200);
        }

        $question->status = 'sent';
        $question->shared_at = Carbon::now()->setTimezone('Asia/Jakarta');
        $question->shared_count = $question->shared_count + 1;
        $question->save();

        return $this->sendResponse($question, 'Berhasil mengubah status question');
    }


    public function destroy($id)
    {
        $userLogin = auth()->user();

        if ($userLogin->role === 'TEACHER') {
            $question = BankQuestion::where('teacher_id', $userLogin->is_teacher->id)->find($id);
        } else {
            $question = BankQuestion::where('status', 'sent')->find($id);
        }

        if (!$question) {
            return $this->sendError('Question tidak ditemukan.', null, 200);
        }

        foreach ($question->question_attachment_banks as $resource) {
            $this->removeFile($resource->file_url);
            $resource->delete();
        }

        $question->delete();

        return $this->sendResponse($question, 'Berhasil menghapus data question');
    }
}
