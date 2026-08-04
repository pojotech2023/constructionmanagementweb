@extends('layouts.app')

@section('content')
<style>
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
}

.chat-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
    max-width: 1100px;
    margin: 0 auto;
    border: 1px solid #ddd;
    background: #fff;
}

.chat-header {
    padding: 10px;
    text-align: center;
    background-color: #f8f9fa;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.chat-body {
    flex: 1 1 auto;
    overflow-y: auto;
    padding: 15px;
    background: #f1f1f1;
    display: flex;
    flex-direction: column;
}

.message-row {
    display: flex;
    margin-bottom: 10px;
}

.message-row.client {
    justify-content: flex-start;
}

.message-row.admin,
.message-row.supervisor {
    justify-content: flex-end;
}

.chat-bubble {
    border-radius: 12px;
    padding: 10px 14px;
    max-width: 70%;
    font-size: 14px;
    line-height: 1.4;
    position: relative;
    word-break: break-word;
}

.chat-bubble.client {
    background: #f8d7da;
    border-top-left-radius: 0;
}

.chat-bubble.admin {
    background: #d1e7dd;
    border-top-right-radius: 0;
}

.chat-bubble.supervisor {
    background: #cfe2ff;
    border-top-left-radius: 0;
}

.chat-meta {
    font-size: 11px;
    color: #6c757d;
    margin-top: 5px;
    text-align: right;
}

.chat-footer {
    padding: 10px;
    background-color: #fff;
    box-shadow: 0 -2px 3px rgba(0,0,0,0.05);
}

textarea.form-control {
    resize: none;
    height: 40px;
}


label[for="attachment"] {
    background: #e9ecef;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-size: 18px;
    cursor: pointer;
    margin-right: 5px;
}
.chat-input-group {
    display: flex;
    align-items: center;
    border: 1px solid #ccc;
    border-radius: 25px;
    padding: 5px 10px;
    background-color: #fff;
}

.attachment-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    background-color: #f1f1f1;
    border-radius: 50%;
    font-size: 20px;
    cursor: pointer;
    margin-right: 8px;
    transition: background-color 0.3s ease;
}

.attachment-icon:hover {
    background-color: #ddd;
}

.chat-textarea {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    resize: none;
    height: 40px;
    flex-grow: 1;
    padding: 5px 10px;
    font-size: 14px;
    background-color: transparent;
}

.send-btn {
    border-radius: 28%;
    width: 70px;
    height: 56px;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-left: 8px;
}


</style>

<div class="chat-container">

    <div class="chat-header">
        <h4 class="fw-bold mb-0">Ticket Chat</h4>
        <small><strong>Site:</strong> {{ $ticket->site->site_name ?? 'N/A' }}</small>
    </div>

    <div class="chat-body">
        @if ($ticket->ticket)
            <div class="message-row client">
                <div class="chat-bubble client">
                    {{ $ticket->ticket }}</br>
                   @if($ticket->file_path)
    <a href="{{ asset('storage/' . $ticket->file_path) }}" target="_blank">
        <img src="{{ asset('storage/' . $ticket->file_path) }}" alt="Attachment" width="150">
    </a>
@endif
                    <div class="chat-meta">
                        Client | {{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y, h:i A') }}
                    </div>
                </div>
            </div>
        @endif

        @foreach ($ticket->messages as $msg)
            <div class="message-row {{ $msg->sender_type }}">
                <div class="chat-bubble {{ $msg->sender_type }}">
                    @if($msg->message)
                        {{ $msg->message }}<br>
                    @endif

                    @if($msg->attachment)
                        <div class="mt-2">
                            @php
                                $ext = strtolower(pathinfo($msg->attachment, PATHINFO_EXTENSION));
                            @endphp

                            @if(in_array($ext, ['jpg', 'jpeg', 'png', 'gif']))
                                <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $msg->attachment) }}" alt="Attachment" style="max-width: 200px;" class="img-fluid rounded"
                                        onerror="this.onerror=null;this.replaceWith(Object.assign(document.createElement('a'),{href:this.src,target:'_blank',className:'btn btn-sm btn-outline-primary',textContent:'📄 View File'}));">
                                </a>
                            @else
                                <a href="{{ asset('storage/' . $msg->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    📄 View File
                                </a>
                            @endif
                        </div>
                    @endif

                    <div class="chat-meta">
                        {{ ucfirst($msg->sender_type) }} | {{ $msg->created_at->format('d M Y, h:i A') }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="chat-footer">
        <form action="{{ route('tickets.admin.storeMessage', $ticket->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div id="attachmentPreview" class="d-none align-items-center mb-2 p-1 px-2" style="background:#f1f3f5; border-radius:8px; width:fit-content;">
                <img id="attachmentPreviewImg" src="" alt="" class="d-none" style="width:32px;height:32px;object-fit:cover;border-radius:4px;margin-right:8px;">
                <span id="attachmentPreviewName" style="font-size:0.85rem; color:#333;"></span>
                <button type="button" id="attachmentRemoveBtn" class="btn btn-sm p-0 ms-2" style="color:#dc3545; line-height:1;">✕</button>
            </div>
            <div class="input-group align-items-center chat-input-group">
            <label for="attachment" class="attachment-icon">📎</label>
            <input type="file" name="attachment" id="attachment" class="d-none">
            <textarea name="message" class="form-control chat-textarea" placeholder="Type your reply..."></textarea>
            <button class="btn btn-success send-btn" type="submit">Send</button>
            </div>
        </form>
    </div>

</div>

<script>
    const attachmentInput = document.getElementById('attachment');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const attachmentPreviewImg = document.getElementById('attachmentPreviewImg');
    const attachmentPreviewName = document.getElementById('attachmentPreviewName');
    const attachmentRemoveBtn = document.getElementById('attachmentRemoveBtn');

    attachmentInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;

        attachmentPreviewName.textContent = file.name;
        attachmentPreview.classList.remove('d-none');
        attachmentPreview.classList.add('d-flex');

        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = e => {
                attachmentPreviewImg.src = e.target.result;
                attachmentPreviewImg.classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        } else {
            attachmentPreviewImg.classList.add('d-none');
        }
    });

    attachmentRemoveBtn.addEventListener('click', function () {
        attachmentInput.value = '';
        attachmentPreviewImg.src = '';
        attachmentPreviewImg.classList.add('d-none');
        attachmentPreview.classList.add('d-none');
        attachmentPreview.classList.remove('d-flex');
    });
</script>
@endsection
