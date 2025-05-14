<?php

namespace App\Http\Controllers\API\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Traits\CommonTrait;
use App\Models\SubmissionAttachment;
use Illuminate\Http\Request;

class AttachmentController extends Controller
{
    use CommonTrait;
    public function destroy(Request $request, $id)
    {
        $attachment = SubmissionAttachment::find($id);

        if (!$attachment) {
            return $this->sendError('Attachment tidak ditemukan.', [], 200);
        }

        $this->removeFile($attachment->file_url);
        $attachment->delete();
        return $this->sendResponse($attachment, 'Berhasil menghapus attachment.');
    }
}
