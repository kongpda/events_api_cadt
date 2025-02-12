<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auth Test Result</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-lg">
            <h1 class="text-2xl font-bold mb-6 text-center">Authentication Result</h1>
            
            @if($success)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                    <div class="flex items-center mb-4">
                        <div class="h-10 w-10 rounded-full overflow-hidden">
                            <img src="{{ $user['avatar'] }}" alt="Profile" class="h-full w-full object-cover">
                        </div>
                        <div class="ml-4">
                            <h2 class="font-semibold">{{ $user['name'] }}</h2>
                            <p class="text-sm text-gray-600">{{ $user['email'] }}</p>
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-sm">
                        <p><strong>Google ID:</strong> <span class="font-mono">{{ $user['id'] }}</span></p>
                        <p><strong>Access Token:</strong> <span class="font-mono break-all">{{ $user['token'] }}</span></p>
                    </div>
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                    <h2 class="text-red-800 font-semibold mb-2">Authentication Failed</h2>
                    <p class="text-red-600">{{ $error }}</p>
                </div>
            @endif

            <div class="mt-6 flex justify-center">
                <a href="{{ route('test.google') }}" 
                   class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                    Try Again
                </a>
            </div>
        </div>
    </div>
</body>
</html> 