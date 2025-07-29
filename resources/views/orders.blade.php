@extends('layout')

@section('content')
    <section class="container-fluid">
        <div class="card">
            <div class="card-body mt-3 p-2">
                <form method="post" action="{{ route('user.check') }}">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-4 mb-3">
                            <div class="row g-3 align-items-center">
                                <div class="col-auto">
                                    <label for="email" class="col-form-label">Select email</label>
                                </div>
                                <div class="col-auto">

                                    <select class="form-select @error('email') is-invalid @enderror" name="email"
                                        value="" onchange="this.form.submit()">
                                        <option value="" hidden>Select User Mail</option>
                                        @foreach ($emails as $userId => $email)
                                            <option value="{{ $userId }}"
                                                {{ old('email') == $userId ? 'selected' : '' }}>
                                                {{ $email }}</option>
                                        @endforeach
                                    </select>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <button type="submit" class="btn btn-primary">
                                submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
