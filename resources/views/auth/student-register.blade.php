<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Student Registration</title>
    <style>
      /* very minimal styling */
      body { font-family: sans-serif; max-width: 400px; margin: 2rem auto; }
      .field  { margin-bottom: 1rem; }
      .error  { color: red; font-size: 0.9rem; }
    </style>
</head>
<body>
    <h2>Student Registration</h2>

    @if ($errors->any())
      <div class="error">
        <ul>
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('register.student.submit') }}">
        @csrf

        <div class="field">
            <label for="name">Full Name</label><br>
            <input
              id="name"
              type="text"
              name="name"
              value="{{ old('name') }}"
              required
              autofocus
            >
            @error('name')
              <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="email">Email Address</label><br>
            <input
              id="email"
              type="email"
              name="email"
              value="{{ old('email') }}"
              required
            >
            @error('email')
              <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="password">Password</label><br>
            <input
              id="password"
              type="password"
              name="password"
              required
            >
            @error('password')
              <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm Password</label><br>
            <input
              id="password_confirmation"
              type="password"
              name="password_confirmation"
              required
            >
        </div>

        <button type="submit">Register</button>
    </form>
</body>
</html>
