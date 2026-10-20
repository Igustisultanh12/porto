<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#101222">
    <script>try{if(localStorage.getItem('theme')==='light')document.documentElement.classList.add('light')}catch(e){}</script>
    @vite('resources/js/app.ts')
    <x-inertia::head />
</head>
<body><x-inertia::app /></body>
</html>