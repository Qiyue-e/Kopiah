@extends('layouts.admin')
@section('title', 'Ganti Password')
@section('content')
<div class="card p-6 max-w-xl">
  <h2 class="font-serif text-2xl">perbarui password</h2>
  <p class="text-sm text-coffee/60 mt-2">gunakan minimal 12 karakter dan jangan membagikan password ke siapa pun.</p>
  <form method="post" action="{{ route('staff.password.update') }}" class="space-y-4 mt-6">
    @csrf @method('PUT')
    <div><label class="field-label" for="current_password">password saat ini</label><input class="field" id="current_password" name="current_password" type="password" required autocomplete="current-password"></div>
    <div><label class="field-label" for="password">password baru</label><input class="field" id="password" name="password" type="password" minlength="12" required autocomplete="new-password"></div>
    <div><label class="field-label" for="password_confirmation">ulangi password baru</label><input class="field" id="password_confirmation" name="password_confirmation" type="password" minlength="12" required autocomplete="new-password"></div>
    <button class="btn btn-coffee" type="submit">Simpan password</button>
  </form>
</div>
@endsection
