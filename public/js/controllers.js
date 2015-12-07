var app = angular.module('controllers', []);

app.controller('UserCtrl', function($scope, User, notify, cache, $compile, Upload) {
    this.data = {};
    this.form = {};
    this.login = function() {
        if (!$scope.login.$valid) {
            notify.alert('danger', 'Chyba pri odosielaní formulára!');
            return;
        }
        User.login(this.data);
    };

    this.logout = function() {
        User.logout();
    };
    this.update = function() {
        User.update(this.form);
    };
    this.register = function() {
        if (!$scope.register.$valid) {
            notify.alert('danger', 'Chyba pri odosielaní formulára!');
            return;
        }
        User.register(this.data);
    };
    this.changePassword = function() {
        if (!$scope.password.$valid) {
            notify.alert('danger', 'Chyba pri odosielaní formulára!');
            return;
        }
        User.changePassword(this.form);
    };

    $scope.uploadFiles = function(file, errFiles) {
        $scope.f = file;
        $scope.errFile = errFiles && errFiles[0];
        if (file) {
            file.upload = Upload.upload({
                url: '/upload',
                data: {file: file}
            });

            file.upload.then(function (response) {
                file.result = response.data;
                if (response.data.success) {
                    notify.alert('success', response.data.success);
                    $('#user-avatar').attr('src', 'img/' + response.data.avatar);
                }
                if (response.data.error) notify.alert('danger', response.data.error);
            }, function (response) {
                if (response.status > 0)
                    $scope.errorMsg = response.status + ': ' + response.data;
            }, function (evt) {
                file.progress = Math.min(100, parseInt(100.0 * 
                                         evt.loaded / evt.total));
            });
        }   
    }

    this.follow = function(id) {
        User.follow(id, 1);
    };

    this.unfollow = function(id) {
        User.follow(id, 2);
    };

    $('[data-toggle="popover"]').popover({html: true}).click(function(ev) {
        $compile($('.popover.in').contents())($scope);
    });
});

app.controller('PostCtrl', function($scope, User, notify, cache) {
    this.data = {};
    this.add = function() {
        if (!$scope.post.$valid) {
            notify.alert('danger', 'Chyba pri odosielaní formulára!');
            return;
        }
        User.addPost(this.data);
    };
});

app.controller('MessageCtrl', function($scope, $http, $location, User, notify, cache) {
    this.data = {};
    this.add = function() {
        if (!$scope.message.$valid) {
            notify.alert('danger', 'Chyba pri odosielaní formulára!');
            return;
        }

        $http.post('/new-message', {receiver: this.data.receiver, title: this.data.title, text: this.data.text}).success(function(data) {
            if (data.errors) {
                notify.alert('danger', data.errors.join('<br>'), 3000);
            } else if (data.success) {
                notify.alert('success', data.success);
                $location.path('/spravy');
                cache.removeReload('partials/messages');
            } else {
                notify.alert('danger', 'Chyba pri odosielaní formulára!<br>Nastala neznáma chyba.');
            }
        });
    };
    this.reply = function(id) {
        if (!$scope.message.$valid) {
            notify.alert('danger', 'Chyba pri odosielaní formulára!');
            return;
        }

        $http.post('/reply', {id: id, text: this.data.text}).success(function(data) {
            if (data.errors) {
                notify.alert('danger', data.errors.join('<br>'), 3000);
            } else if (data.success) {
                notify.alert('success', data.success);
                cache.removeReload('/partials/amessage/' + id);
                $location.path('sprava/' + id);
            } else {
                notify.alert('danger', 'Chyba pri odosielaní formulára!<br>Nastala neznáma chyba.');
            }
        });
    };
    this.reload = function() {
        cache.removeAllReload();
    };
});
