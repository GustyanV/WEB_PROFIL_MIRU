@csrf
@if($activity->exists) @method('PUT') @endif
@if($errors->any())<div role="alert" class="rounded-xl bg-red-50 p-4 text-sm text-red-700"><ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="grid gap-4 sm:grid-cols-2">
@foreach(['title'=>'Judul','slug'=>'Slug','activity_date'=>'Tanggal Kegiatan','published_at'=>'Published At'] as $field=>$label)
<label class="form-label" for="{{ $field }}">{{ $label }}
<input id="{{ $field }}" name="{{ $field }}" type="{{ $field === 'activity_date' ? 'date' : ($field === 'published_at' ? 'datetime-local' : 'text') }}" value="{{ old($field, $field === 'activity_date' ? $activity->activity_date?->format('Y-m-d') : ($field === 'published_at' ? $activity->published_at?->format('Y-m-d\TH:i') : $activity->$field)) }}" @required(in_array($field,['title','activity_date'])) class="form-control"></label>
@endforeach
<label class="form-label" for="category">Kategori<select id="category" name="category" required class="form-control">@foreach(\App\Models\Activity::CATEGORIES as $category)<option @selected(old('category',$activity->category) === $category)>{{ $category }}</option>@endforeach</select></label>
<label class="form-label" for="status">Status<select id="status" name="status" required class="form-control">@foreach(['draft','published'] as $status)<option @selected(old('status',$activity->status) === $status)>{{ $status }}</option>@endforeach</select></label>
</div>
<p class="text-xs text-slate-500">Slug otomatis dari judul jika dikosongkan. Published At di masa depan menjadwalkan publikasi; kosong berarti langsung tampil jika status published.</p>
@foreach(['excerpt'=>'Ringkasan','content'=>'Isi Kegiatan'] as $field=>$label)
<label for="{{ $field }}" class="form-label">{{ $label }}<textarea id="{{ $field }}" name="{{ $field }}" rows="{{ $field === 'content' ? 10 : 3 }}" @required($field === 'content') class="form-control">{{ old($field,$activity->$field) }}</textarea></label>
@endforeach
<p class="text-xs text-slate-500">Isi ditampilkan sebagai teks. Baris baru dipertahankan.</p>
<label for="thumbnail" class="form-label">Thumbnail<input type="file" id="thumbnail" name="thumbnail" accept=".jpg,.jpeg,.png,.webp" class="mt-2 block w-full"></label>
<p class="text-xs text-slate-500">Maksimal 2 MB, JPG/JPEG/PNG/WEBP. Kosongkan untuk mempertahankan gambar; tanpa thumbnail akan memakai cover galeri.</p>
@if($activity->exists)<img src="{{ $activity->image_url }}" alt="Thumbnail saat ini" class="h-40 w-64 rounded-xl object-cover">@endif
<button class="btn-primary">SIMPAN KEGIATAN</button>