@csrf
@if($socialMedia->exists) @method('PUT') @endif
<div><label for="platform" class="text-sm font-bold">Platform</label><select id="platform" name="platform" required class="mt-1 w-full rounded-xl border-slate-200">
@foreach(\App\Models\SocialMedia::PLATFORMS as $value=>$label)<option value="{{ $value }}" @selected(old('platform', $socialMedia->platform) === $value)>{{ $label }}</option>@endforeach
</select>@error('platform')<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
<div><label for="url" class="text-sm font-bold">URL</label><input type="url" id="url" name="url" value="{{ old('url', $socialMedia->url) }}" maxlength="2048" class="mt-1 w-full rounded-xl border-slate-200">@error('url')<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
<div><label for="sort_order" class="text-sm font-bold">Urutan</label><input id="sort_order" name="sort_order" type="number" min="0" max="2147483647" required value="{{ old('sort_order', $socialMedia->sort_order) }}" class="mt-1 w-full rounded-xl border-slate-200">@error('sort_order')<p class="text-sm text-red-600">{{ $message }}</p>@enderror</div>
<input type="hidden" name="is_active" value="0">
<label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $socialMedia->is_active))> Aktif</label>
@error('is_active')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
<button class="rounded-xl bg-primary px-5 py-3 font-bold text-white">SIMPAN MEDIA SOSIAL</button>