@foreach(['password'=>'Password','password_confirmation'=>'Konfirmasi Password'] as $field=>$label)
<label for="{{ $field }}" class="form-label">{{ $label }}<input id="{{ $field }}" type="password" name="{{ $field }}" required minlength="8" autocomplete="new-password" class="form-control"></label>
@endforeach