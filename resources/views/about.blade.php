@extends('layouts.app')

@section('title', 'À propos')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-6 text-center">
            <i class="fas fa-user-circle text-purple-600"></i> À propos
        </h1>

        <div class="flex flex-col md:flex-row items-center md:items-start space-y-6 md:space-y-0 md:space-x-8">
            <!-- Photo de profil / Avatar -->
            <div class="flex-shrink-0">
                <div class="w-48 h-48 bg-gradient-to-br from-purple-600 via-pink-600 to-purple-800 rounded-full flex items-center justify-center shadow-xl">
                    <i class="fas fa-user-graduate text-white text-6xl"></i>
                </div>
            </div>

            <!-- Informations principales -->
            <div class="flex-1">
                <h2 class="text-3xl font-bold text-gray-800 mb-2">SALAHOU Chaydatou</h2>
                <p class="text-xl text-purple-600 font-semibold mb-4">
                    <i class="fas fa-graduation-cap mr-2"></i>Étudiante en Informatique
                </p>

                <p class="text-gray-700 mb-4 leading-relaxed">
                    Bonjour ! Je suis <strong>SALAHOU Chaydatou</strong>, étudiante passionnée par le 
                    <strong>développement web</strong> et l'<strong>intelligence artificielle</strong>. 
                    J'aime particulièrement apprendre de nouvelles technologies, créer des projets innovants 
                    et améliorer constamment mes compétences en programmation.
                </p>

                <p class="text-gray-700 mb-6 leading-relaxed">
                    J'ai créé <strong class="text-purple-600">Buzz Events</strong> pour permettre à chacun 
                    de partager et découvrir les événements qui font le buzz sur internet. Ce projet reflète 
                    ma passion pour le développement d'applications web modernes et intuitives.
                </p>

                <!-- Passions et intérêts -->
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 border-l-4 border-purple-600 p-4 mb-6 rounded">
                    <h3 class="font-bold text-purple-800 mb-3 flex items-center">
                        <i class="fas fa-heart text-pink-600 mr-2"></i>Mes passions
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-gray-700">
                        <div class="flex items-center">
                            <i class="fas fa-code text-purple-600 mr-2"></i>
                            <span>Développement Web</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-brain text-pink-600 mr-2"></i>
                            <span>Intelligence Artificielle</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-book-reader text-blue-600 mr-2"></i>
                            <span>Apprentissage continu</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-project-diagram text-green-600 mr-2"></i>
                            <span>Création de projets</span>
                        </div>
                    </div>
                </div>

                <!-- Technologies -->
                <div class="bg-purple-50 border-l-4 border-purple-600 p-4 mb-6 rounded">
                    <h3 class="font-bold text-purple-800 mb-3 flex items-center">
                        <i class="fas fa-tools mr-2"></i>Technologies utilisées
                    </h3>
                    <div class="grid grid-cols-2 gap-3 text-gray-700">
                        <div class="flex items-center">
                            <i class="fab fa-laravel text-red-600 mr-2 text-xl"></i>
                            <span>Laravel</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fab fa-html5 text-orange-600 mr-2 text-xl"></i>
                            <span>HTML5 / CSS3</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-wind text-blue-400 mr-2 text-xl"></i>
                            <span>Tailwind CSS</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-database text-blue-600 mr-2 text-xl"></i>
                            <span>MySQL</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fab fa-docker text-blue-500 mr-2 text-xl"></i>
                            <span>Docker</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fab fa-php text-indigo-600 mr-2 text-xl"></i>
                            <span>PHP</span>
                        </div>
                    </div>
                </div>

                <!-- Réseaux sociaux -->
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-800 hover:text-purple-600 text-2xl transition" title="GitHub">
                        <i class="fab fa-github"></i>
                    </a>
                    <a href="#" class="text-blue-600 hover:text-blue-800 text-2xl transition" title="LinkedIn">
                        <i class="fab fa-linkedin"></i>
                    </a>
                    <a href="#" class="text-pink-600 hover:text-pink-800 text-2xl transition" title="Email">
                        <i class="fas fa-envelope"></i>
                    </a>
                    <a href="#" class="text-purple-600 hover:text-purple-800 text-2xl transition" title="Portfolio">
                        <i class="fas fa-globe"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Section Objectif -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <h3 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-bullseye text-purple-600 mr-3"></i>
                Objectif du projet
            </h3>
            <p class="text-gray-700 leading-relaxed mb-4">
                <strong>Buzz Events</strong> vise à créer une plateforme collaborative où les internautes 
                peuvent partager et découvrir rapidement les tendances et actualités qui font vibrer le web. 
                Chaque événement est soigneusement présenté avec une image, une description concise et un lien 
                direct vers la source pour approfondir le sujet.
            </p>
            <p class="text-gray-700 leading-relaxed">
                Ce projet me permet de mettre en pratique mes compétences en développement web tout en créant 
                une application utile et engageante pour la communauté. C'est également une opportunité 
                d'explorer les meilleures pratiques de développement Laravel et de conception d'interfaces 
                utilisateur modernes.
            </p>
        </div>

        <!-- Section Compétences -->
        <div class="mt-8 pt-8 border-t border-gray-200">
            <h3 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-line text-purple-600 mr-3"></i>
                Compétences en développement
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">Backend</h4>
                    <p class="text-gray-600 text-sm">Laravel, PHP, API REST, Base de données MySQL</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">Frontend</h4>
                    <p class="text-gray-600 text-sm">HTML5, CSS3, Tailwind CSS, JavaScript</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">DevOps</h4>
                    <p class="text-gray-600 text-sm">Docker, Git, Déploiement Railway</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-2">IA & ML</h4>
                    <p class="text-gray-600 text-sm">Apprentissage machine, Deep Learning, Python</p>
                </div>
            </div>
        </div>

        <!-- Call to action -->
        <div class="mt-8 pt-8 border-t border-gray-200 text-center">
            <p class="text-gray-600 mb-4">Intéressé(e) par ce projet ou envie d'échanger ?</p>
            <a href="mailto:votre.email@example.com" 
               class="inline-block bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold px-8 py-3 rounded-lg hover:from-purple-700 hover:to-pink-700 transition shadow-lg">
                <i class="fas fa-envelope mr-2"></i>Me contacter
            </a>
        </div>
    </div>
</div>
@endsection