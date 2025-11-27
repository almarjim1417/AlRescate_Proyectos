import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:url_launcher/url_launcher.dart';
// IMPORTANTE: Necesitas agregar 'webview_flutter: ^4.8.0' en pubspec.yaml
import 'package:webview_flutter/webview_flutter.dart'; 

/*
  INSTRUCCIONES DE CONFIGURACIÓN:

  1. Agrega las dependencias a tu `pubspec.yaml`:
     dependencies:
       flutter:
         sdk: flutter
       mobile_scanner: ^5.2.0
       url_launcher: ^6.3.0
       webview_flutter: ^4.8.0  <-- NUEVA DEPENDENCIA NECESARIA

  2. Ejecuta 'flutter pub get'.
  
  3. Si usas Android, asegúrate en 'android/app/build.gradle' que:
     minSdkVersion 19 (o superior)
*/

// --- DEFINICIÓN DE COLORES CORPORATIVOS ---
const Color kColorPrincipal = Color(0xFF206E90); 
const Color kColorAcento = Color(0xFFF05A39);    

void main() {
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Escáner QR',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: kColorPrincipal),
        useMaterial3: true,
        appBarTheme: const AppBarTheme(
          backgroundColor: kColorPrincipal,
          foregroundColor: Colors.white,
          centerTitle: true,
        ),
      ),
      home: const QRScannerPage(),
    );
  }
}

class QRScannerPage extends StatefulWidget {
  const QRScannerPage({super.key});

  @override
  State<QRScannerPage> createState() => _QRScannerPageState();
}

class _QRScannerPageState extends State<QRScannerPage> {
  final MobileScannerController _scannerController = MobileScannerController(
    formats: [BarcodeFormat.qrCode],
    facing: CameraFacing.back,
  );

  bool _isProcessing = false;

  @override
  void dispose() {
    _scannerController.dispose();
    super.dispose();
  }

  Future<void> _launchURL(String url) async {
    // 1. Verificamos si es una URL válida
    final Uri uri = Uri.parse(url);
    bool esWeb = url.startsWith('http://') || url.startsWith('https://');

    if (esWeb) {
      // 2. EN LUGAR DE ABRIR CHROME, ABRIMOS NUESTRO NAVEGADOR INTERNO
      // Esto permite mantener las cookies y la sesión iniciada.
      
      // Esperamos un momento para UX
      await Future.delayed(const Duration(milliseconds: 500));
      
      if (mounted) {
        // Navegamos a la pantalla del navegador interno
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => InAppWebViewPage(url: url),
          ),
        ).then((_) {
          // Cuando vuelvan del navegador, reactivamos el escáner
          setState(() {
            _isProcessing = false;
          });
        });
      }
    } else {
      // Si no es web (ej: mailto, tel), intentamos abrirlo con el sistema
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        _showError('No se pudo abrir el enlace: $url');
      }
      
      setState(() {
        _isProcessing = false;
      });
    }
  }

  void _showError(String message) {
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(message),
          backgroundColor: Colors.redAccent,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final double scanSize = MediaQuery.of(context).size.width * 0.6;

    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'AlRescate',
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        backgroundColor: kColorPrincipal, 
      ),
      body: Column(
        children: [
          const SizedBox(height: 20),
          
          // --- SECCIÓN DEL LOGO ---
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Expanded(
                child: Container(
                  height: 100,
                  padding: const EdgeInsets.symmetric(horizontal: 16.0),
                  child: Image.asset(
                    'assets/images/logo_alrescate.png',
                    fit: BoxFit.contain,
                    alignment: Alignment.center,
                    errorBuilder: (context, error, stackTrace) {
                      return const Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.error_outline, size: 40, color: kColorAcento),
                          Text("No se carga la imagen", style: TextStyle(fontSize: 10)),
                        ],
                      );
                    },
                  ),
                ),
              ),
            ],
          ),
          
          const SizedBox(height: 10),
          
          const Text(
            'Lector QR',
            style: TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: kColorPrincipal, 
            ),
          ),
          
          Expanded(
            child: Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  // --- RECUADRO DEL ESCÁNER ---
                  Container(
                    width: scanSize,
                    height: scanSize,
                    decoration: BoxDecoration(
                      border: Border.all(color: kColorAcento, width: 5), 
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(15),
                      child: Stack(
                        alignment: Alignment.center,
                        children: [
                          MobileScanner(
                            controller: _scannerController,
                            onDetect: (capture) {
                              if (_isProcessing) return;
                              final String? code = capture.barcodes.first.rawValue;
                              if (code != null) {
                                setState(() {
                                  _isProcessing = true;
                                });
                                _launchURL(code);
                              }
                            },
                          ),
                          
                          // Botón Linterna
                          Positioned(
                            bottom: 8,
                            right: 8,
                            child: Container(
                              decoration: const BoxDecoration(
                                color: Colors.black45,
                                shape: BoxShape.circle,
                              ),
                              child: IconButton(
                                iconSize: 24,
                                color: Colors.white,
                                icon: ValueListenableBuilder(
                                  valueListenable: _scannerController,
                                  builder: (context, state, child) {
                                    switch (state.torchState) {
                                      case TorchState.off:
                                        return const Icon(Icons.flash_off, color: Colors.white70);
                                      case TorchState.on:
                                        return const Icon(Icons.flash_on, color: Colors.yellow); 
                                      default:
                                        return const Icon(Icons.flash_off, color: Colors.white70);
                                    }
                                  },
                                ),
                                onPressed: () => _scannerController.toggleTorch(),
                              ),
                            ),
                          ),

                          if (_isProcessing)
                            Container(
                              color: Colors.black54,
                              child: const Center(
                                child: CircularProgressIndicator(
                                  color: Colors.white,
                                ),
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 20),
                  
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    decoration: BoxDecoration(
                      color: Colors.grey[200],
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      'Apunta la cámara al código QR',
                      style: TextStyle(
                        color: Colors.black87,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// --- NUEVA CLASE: PANTALLA DE NAVEGADOR INTERNO ---
class InAppWebViewPage extends StatefulWidget {
  final String url;

  const InAppWebViewPage({super.key, required this.url});

  @override
  State<InAppWebViewPage> createState() => _InAppWebViewPageState();
}

class _InAppWebViewPageState extends State<InAppWebViewPage> {
  late final WebViewController _controller;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    
    // Inicializamos el controlador del WebView
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted) // Permitir JS es vital para logins
      ..setBackgroundColor(const Color(0x00000000))
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (String url) {
            if (mounted) setState(() => _isLoading = true);
          },
          onPageFinished: (String url) {
            if (mounted) setState(() => _isLoading = false);
          },
          onWebResourceError: (WebResourceError error) {
            // Manejo básico de errores
          },
        ),
      )
      ..loadRequest(Uri.parse(widget.url));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text("Navegador"),
        backgroundColor: kColorPrincipal,
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: () => Navigator.of(context).pop(), // Botón para cerrar y volver al escáner
        ),
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (_isLoading)
            const Center(
              child: CircularProgressIndicator(color: kColorPrincipal),
            ),
        ],
      ),
    );
  }
}