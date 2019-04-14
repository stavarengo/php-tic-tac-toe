(function (w) {
    w.ttt = {
        VALID_UNITS: ['X', 'O'],

        nodes: {},

        gameState: null,

        init: function () {
            var me = this;

            me.nodes.startNewGame = me.byId('start-new-game');
            me.nodes.startNewGameHumanO = me.byId('start-game-human-o');
            me.nodes.startNewGameHumanX = me.byId('start-game-human-x');
            me.nodes.toastTitle = me.byId('global-toast-title');
            me.nodes.toastBody = me.byId('global-toast-body');
            me.nodes.boardSection = me.byId('board-section');
            me.nodes.resultSection = me.byId('result-section');
            me.nodes.result = me.byId('result');
            me.nodes.prompt = me.byId('prompt');

            for (var row = 0; row < 3; row++) {
                for (var column = 0; column < 3; column++) {
                    var cell = me.byId('cell-' + row + '-' + column);
                    me.nodes['cell' + row + '' + column] = cell;

                    cell.onclick = me.setHumanMove.bind(me, row, column);
                }
            }

            me.nodes.startNewGameHumanO.onclick = function () {
                me.startNewGame(me.VALID_UNITS[me.VALID_UNITS.indexOf('O')]);
            };

            me.nodes.startNewGameHumanX.onclick = function () {
                me.startNewGame(me.VALID_UNITS[me.VALID_UNITS.indexOf('X')]);
            };

            me.updateView(w.__INITIAL_STATE__ || null);
        },

        request: function (method, url, callback, data) {
            var request = new XMLHttpRequest();

            request.open(method, url, true);

            if (method === 'POST' || method === 'PUT') {
                request.setRequestHeader('Content-Type', 'application/json');
            }

            request.onreadystatechange = function () {
                if (request.readyState === XMLHttpRequest.DONE) {
                    callback(request)
                }
            };

            request.send(data);
        },

        byId: function (id) {
            return document.getElementById(id);
        },

        showError: function (title, msg) {
            this.nodes.toastTitle.innerHTML = title;
            this.nodes.toastBody.innerHTML = msg;
            $('#global-toast').toast('show')
        },

        updateView: function (gameState) {
            this.gameState = gameState;
            var game = gameState && gameState.game;
            var row, column, cellValue;
            if (!game) {
                this.nodes.startNewGame.classList.remove('d-none');
                this.nodes.boardSection.classList.add('d-none');
                this.nodes.resultSection.classList.add('d-none');

                for (row = 0; row < 3; row++) {
                    for (column = 0; column < 3; column++) {
                        cell = this.nodes['cell' + row + '' + column];
                        cell.innerHTML = '';
                        cell.classList.remove('winner-cell');
                    }
                }

                return;
            }

            this.nodes.startNewGame.classList.add('d-none');
            this.nodes.boardSection.classList.remove('d-none');

            if (!game.winner) {
                this.nodes.resultSection.classList.add('d-none');
            }

            var cell;
            for (row = 0; row < 3; row++) {
                for (column = 0; column < 3; column++) {
                    cellValue = gameState.game.board[row][column];
                    cell = this.nodes['cell' + row + '' + column];

                    if (cellValue === this.VALID_UNITS[0]) {
                        cell.innerHTML = '<i class="fas fa-times fa-3x"></i>';
                    } else if (cellValue === this.VALID_UNITS[1]) {
                        cell.innerHTML = '<i class="far fa-circle fa-3x"></i>';
                    }
                }
            }

            if (game.winner) {
                this.nodes.result.innerHTML = '<span>Robot won!</span><i class="fas fa-robot fa-2x ml-4"></i>';
                if (game.winner.result === game.units.human) {
                    this.nodes.result.innerHTML = 'You win! <i class="far fa-surprise fa-2x ml-4"></i>';
                }
                this.nodes.prompt.innerHTML = '<h5>Do you want to play again?<br>Choose your symbol</h5>';
                this.nodes.resultSection.classList.remove('d-none');
                this.nodes.startNewGame.classList.remove('d-none');
                this.nodes.startNewGame.classList.add('bg-light');

                if (game.winner.coordinates) {
                    for (var c = 0; c < game.winner.coordinates.length; c++) {
                        var coordinate = game.winner.coordinates[c];
                        cell = this.nodes['cell' + coordinate[0] + '' + coordinate[1]];
                        cell.classList.add('winner-cell');
                    }

                }
            }
        },

        defaultRequestErrorHandler: function (request, errorMessageTitle, expectedStatusCode) {
            if (request.status > 399 && request.status < 500) {
                var error = JSON.parse(request.responseText);
                this.showError(errorMessageTitle, error.detail)
            } else {
                this.showError(
                    errorMessageTitle,
                    'We got an unexpected response code from server.<br>' +
                    '<small><code>Was expecting ' + expectedStatusCode + ', but got ' + request.status + '</code>.</small>'
                );
            }
        },

        deleteGame: function (callback, errback) {
            var me = this;
            me.request(
                'DELETE',
                '/api/board',
                function (request) {
                    if (request.status === 204) {
                        me.updateView(null)
                        callback();
                    } else {
                        errback(request);
                    }
                }
            );
        },

        startNewGame: function (humanUnit) {
            var me = this;
            if (me.nodes.startNewGame.className.indexOf('starting-game') > -1) {
                return;
            }

            if (me.gameState && me.gameState.game && me.gameState.game.winner) {
                me.deleteGame(me.startNewGame.bind(me, me.gameState.game.units.human))

            }

            me.nodes.startNewGame.classList.add('starting-game');

            me.request(
                'POST',
                '/api/board',
                function (request) {
                    if (request.status === 201) {
                        me.updateView(JSON.parse(request.responseText))
                    } else {
                        me.defaultRequestErrorHandler(request, 'Why the game did not start?', 201);
                    }

                    me.nodes.startNewGame.classList.remove('starting-game');
                },
                JSON.stringify({
                    humanUnit: humanUnit,
                    botUnit: humanUnit === me.VALID_UNITS[0] ? me.VALID_UNITS[1] : me.VALID_UNITS[0]
                })
            );
        },

        setHumanMove: function (row, column) {
            var me = this;

            me.request(
                'PUT',
                '/api/board',
                function (request) {
                    if (request.status === 200) {
                        me.updateView(JSON.parse(request.responseText))
                    } else {
                        me.defaultRequestErrorHandler(request, 'What went wrong?', 200);
                    }
                },
                JSON.stringify({
                    row: row,
                    column: column
                })
            );
        }
    };

    ttt.init();

})(window);
