<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Enviada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('{{ asset("images/fondo.png") }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-family: 'Helvetica', 'Arial', sans-serif;
        }
        
        .confirmation-container {
            width: 100%;
            max-width: 650px;
            transform: scale(0.98);
        }
        
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
            background-color: white;
            overflow: hidden;
        }
        
        .card-header {
            background-color: #FF6B24;
            color: white;
            padding: 1.2rem 1.5rem;
            text-align: center;
            border: none;
        }
        
        .card-header h3 {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .card-body {
            padding: 2.5rem 1.5rem;
            text-align: center;
        }
        
        .success-icon {
            color: #FF6B24;
            margin-bottom: 1.5rem;
        }
        
        .confirmation-title {
            font-weight: bold;
            color: #212529;
            margin-bottom: 1rem;
        }
        
        .confirmation-text {
            color: #555;
            margin-bottom: 0.5rem;
        }
        
        .confirmation-details {
            color: #777;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }
        
        .btn-primary {
            background-color: #FF6B24;
            border: none;
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            font-weight: bold;
            transition: background-color 0.2s;
        }
        
        .btn-primary:hover {
            background-color: #e45e1f;
        }
        
        .btn-outline-primary {
            color: #FF6B24;
            border-color: #FF6B24;
            border-radius: 50px;
            padding: 0.6rem 1.5rem;
            font-weight: bold;
        }
        
        .btn-outline-primary:hover {
            background-color: #FF6B24;
            color: white;
        }
        
        .footer-logos {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .footer-logos img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="card">
            <div class="card-header">
                <h3>¡Solicitud Enviada!</h3>
            </div>
            <div class="card-body">
                <div class="success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="90" height="90" fill="currentColor" class="bi bi-check-circle-fill" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                </div>
                
                <h4 class="confirmation-title">Su solicitud de préstamo ha sido recibida</h4>
                
                <p class="confirmation-text">Hemos registrado su solicitud correctamente.</p>
                <p class="confirmation-details">Un representante revisará su solicitud en breve y le informaremos el estado a través del correo electrónico proporcionado.</p>
                
                {{-- <div class="mt-4">
                    <a href="{{ route('public.loan-request.form') }}" class="btn btn-outline-primary me-2">Realizar otra solicitud</a>
                    <a href="/" class="btn btn-primary">Volver al inicio</a>
                </div> --}}
            </div>
        </div>
        
        <div class="footer-logos">
            <img src="{{ asset('images/logos.png') }}" alt="Logos corporativos">
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>