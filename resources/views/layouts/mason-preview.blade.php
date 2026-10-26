<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name') }} - {{ trans('ip.report_preview') }}</title>
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @masonStyles
        
        <style>
            body {
                font-family: 'Arial', sans-serif;
                background: #f3f4f6;
                padding: 1rem;
                margin: 0;
            }
            
            #mason-preview-container {
                --mason-border-color: rgb(59, 130, 246);
                --mason-controls-background: rgba(0, 0, 0, 0.8);
                --mason-button-hover-background: rgba(255, 255, 255, 0.2);
                --mason-drop-zone-background: rgba(59, 130, 246, 0.5);
            }

            .report-preview {
                max-width: 210mm; /* A4 width */
                margin: 0 auto;
                background: white;
                padding: 20mm;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            }
        </style>
    </head>
    <body>
        <main>
            <div class="report-preview">
                @include('mason::iframe-preview-content', ['blocks' => $blocks])
            </div>
        </main>
    </body>
</html>
