var APP_NAME = 'Hejtuj.sk';
var app = angular.module('Hate', ['services', 'controllers', 'directives', 'ngAnimate', 'ngRoute', 'ngFileUpload', 'cfp.loadingBar', 'ngMessages']);
var links = [
    {url: '/', title: APP_NAME, partial: 'home'},
    {url: '/mapa', title: APP_NAME, content: 'home'},
    {url: '/prihlasenie', title: 'Prihlásenie', partial: 'login'},
    {url: '/registracia', title: 'Registrácia', partial: 'register'},
    {url: '/nastavenia', title: 'Nastavenia', partial: 'settings'},
    {url: '/profil', title: 'Úprava profilu', partial: 'profile'},
    {url: '/kontakt', title: 'Kontakt', partial: 'contact'},
    {url: '/uzivatelia', title: 'Užívatelia', partial: 'users'},
    {url: '/prispevky-sledujuci', title: 'Príspevky od sledujúcich', partial: 'followers-posts'},
    {url: '/moje-prispevky', title: 'Moje príspevky', partial: 'my-posts'},
    {url: '/spravy', title: 'Správy', partial: 'messages'},
];


app.run(['$location', '$rootScope', 'User', 'cfpLoadingBar', 'cache', function($location, $rootScope, User, cfpLoadingBar, cache) {
    $rootScope.logged = state;
    $rootScope.user = user;
    $rootScope.$on('$routeChangeSuccess', function (event, current, previous) {
        if (current.loadedTemplateUrl == 'partials/home') {
            $rootScope.title = current.$$route.title;
            cache.remove(current.$$route.templateUrl);
        } else {
            $rootScope.title = current.$$route.title + ' - ' + APP_NAME;
        }
        cfpLoadingBar.complete();

        if (current.loadedTemplateUrl == 'partials/followers-posts' || current.loadedTemplateUrl == 'partials/my-posts' || current.loadedTemplateUrl.search('partials/amessage')) cache.remove(current.$$route.templateUrl);
    });

    $rootScope.$on('$routeChangeStart', function(event, next, current) {
        cfpLoadingBar.start();
        cfpLoadingBar.set(0.5);

        $('body, html').animate({
            scrollTop: 0
        }, 500);
    });

    $rootScope.$on('$routeChangeError', function(event, next, current) {
        $location.path('/404');
    });

    setInterval(function() {
        if (state) User.checkNotifications();
    }, 1000);
}]);


app.config(function($routeProvider, $locationProvider, $interpolateProvider, cfpLoadingBarProvider) {
    cfpLoadingBarProvider.includeSpinner = false;

    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');

    $routeProvider.when('/uzivatel/:id', {
        templateUrl: function(params){ return '/partials/user/' + params.id; },
        controller: 'UserCtrl',
        title: 'Používateľ'
    });

    $routeProvider.when('/uzivatel/:id/prispevky', {
        templateUrl: function(params){ return '/partials/posts/' + params.id; },
        controller: 'UserCtrl',
        title: 'Príspevky'
    });

    $routeProvider.when('/uzivatel/:id/sledujuci', {
        templateUrl: function(params){ return '/partials/following/' + params.id; },
        controller: 'UserCtrl',
        title: 'Sledujúci'
    });

    $routeProvider.when('/uzivatel/:id/sledovatelia', {
        templateUrl: function(params){ return '/partials/followers/' + params.id; },
        controller: 'UserCtrl',
        title: 'Sledovatelia'
    });

    $routeProvider.when('/prispevok/:id', {
        templateUrl: function(params){ return '/partials/post/' + params.id; },
        controller: 'PostCtrl',
        title: 'Príspevok'
    });

    $routeProvider.when('/nova-sprava/:id', {
        templateUrl: function(params){ return '/partials/new-message/' + params.id; },
        controller: 'MessageCtrl',
        title: 'Nová správa'
    });

    $routeProvider.when('/sprava/:id', {
        templateUrl: function(params){ return '/partials/amessage/' + params.id; },
        controller: 'PostCtrl',
        title: 'Správa'
    });

    $routeProvider.when('/odpovedat/:id', {
        templateUrl: function(params){ return '/partials/reply/' + params.id; },
        controller: 'PostCtrl',
        title: 'Odpovedať na správu'
    });

    links.map(function(item) {
        if (item.partial) {
            $routeProvider.when(item.url, {
                title: item.title,
                templateUrl: 'partials/'+item.partial
            });
        } else {
            $routeProvider.when(item.url, {
                title: item.title,
                template: item.content
            });

        }

    });

    $routeProvider.otherwise({
        redirectTo: '/'
    });

    $locationProvider.html5Mode(true);
    $locationProvider.hashPrefix('!');
});