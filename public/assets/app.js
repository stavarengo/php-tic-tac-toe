(function (w) {
    w.ttt = {
        VALID_UNITS: ['X', 'O'],

        nodes: {},

        gameState: null,

        init: function () {
            this.nodes.spinner = this.byId('spinner');
            this.nodes.boardSection = this.byId('board-section');
            this.nodes.footerSection = this.byId('footer-section');
            this.nodes.resultSection = this.byId('result-section');
            this.nodes.result = this.byId('result');
            this.nodes.startNewGame = this.byId('start-new-game');
            this.nodes.startNewGameHumanO = this.byId('start-game-human-o');
            this.nodes.startNewGameHumanX = this.byId('start-game-human-x');
            this.nodes.prompt = this.byId('prompt');
            this.nodes.toastTitle = this.byId('global-toast-title');
            this.nodes.toastBody = this.byId('global-toast-body');

            for (var row = 0; row < 3; row++) {
                for (var column = 0; column < 3; column++) {
                    var cell = this.byId('cell-' + row + '-' + column);
                    this.nodes['cell' + row + '' + column] = cell;

                    cell.onclick = this.cellNodeClick.bind(this, row, column);
                }
            }

            this.nodes.startNewGameHumanO.onclick = this.startNewGameNodeClick.bind(this, this.VALID_UNITS[this.VALID_UNITS.indexOf('O')]);
            this.nodes.startNewGameHumanX.onclick = this.startNewGameNodeClick.bind(this, this.VALID_UNITS[this.VALID_UNITS.indexOf('X')]);

            this.updateView(w.__INITIAL_STATE__ || null);
        },


        request: function (method, url, callback, data) {
            var me = this;

            if (me.request._inProgress) {
                return
            }
            me.request._inProgress = true;

            me.nodes.spinner.classList.add('show');

            var request = new XMLHttpRequest();

            request.open(method, url, true);

            if (method === 'POST' || method === 'PUT') {
                request.setRequestHeader('Content-Type', 'application/json');
            }

            var startTime = Date.now();
            var requestCallback = function(request) {
                me.request._inProgress = false;
                callback(request);
                if (!me.request._inProgress) {
                    me.nodes.spinner.classList.remove('show');
                }
            };

            request.onreadystatechange = function () {
                if (request.readyState === XMLHttpRequest.DONE) {
                    if (method === 'PUT') {
                        // Why are we delaying the PUT response?
                        //   The PUT request is when the bot make its decision, and most of the time, it make the
                        //   decision too, too fast.
                        //   During some "watch tests" (where I ask somebody to use and just watch them), one of these
                        //   users asked me "why the machine was playing in the same time she was?".
                        //   So, I thought to give it at least small milliseconds of delay, so no other user would get
                        //   confused about this, and the others users (who did not get confuse) would even notice it.
                        var timeElapsed = Date.now() - startTime;
                        let minimumTimeToThink = 150;
                        if (timeElapsed < minimumTimeToThink) {
                            setTimeout(requestCallback.bind(null, request), minimumTimeToThink);
                            return;
                        }
                    }

                    requestCallback(request);
                }
            };

            request.send(data);
        },

        deleteGame: function (callback) {
            var me = this;
            me.request(
                'DELETE',
                '/api/board',
                function (request) {
                    if (request.status === 204) {
                        callback();
                    } else {
                        me.defaultRequestErrorHandler(request, 'Why the game did not start?', 204);
                    }
                }
            );
        },

        startNewGame: function (humanUnit) {
            var me = this;

            me.request(
                'POST',
                '/api/board',
                function (request) {
                    if (request.status === 201) {
                        me.updateView(JSON.parse(request.responseText))
                    } else {
                        me.defaultRequestErrorHandler(request, 'Why the game did not start?', 201);
                    }
                },
                JSON.stringify({
                    humanUnit: humanUnit,
                    botUnit: humanUnit === me.VALID_UNITS[0] ? me.VALID_UNITS[1] : me.VALID_UNITS[0]
                })
            );
        },

        setHumanMove: function (row, column, errback) {
            var me = this;

            me.request(
                'PUT',
                '/api/board',
                function (request) {
                    if (request.status === 200) {
                        me.updateView(JSON.parse(request.responseText))
                    } else {
                        me.defaultRequestErrorHandler(request, 'What went wrong?', 200);
                        errback();
                    }
                },
                JSON.stringify({
                    row: row,
                    column: column
                })
            );
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
                this.nodes.boardSection.classList.add('d-none');
                this.nodes.footerSection.classList.remove('d-none');
                this.nodes.resultSection.classList.add('d-none');
                this.nodes.startNewGame.classList.remove('d-none');
                return;
            }

            var cell;
            for (row = 0; row < 3; row++) {
                for (column = 0; column < 3; column++) {
                    cellValue = gameState.game.board[row][column];
                    cell = this.nodes['cell' + row + '' + column];

                    if (!game.winner) {
                        cell.classList.remove('winner-cell');
                    }

                    if (cellValue) {
                        cell.classList.remove('empty');
                    } else {
                        cell.classList.add('empty');
                    }

                    if (cellValue === this.VALID_UNITS[0]) {
                        cell.innerHTML = '<i class="fas fa-times fa-4x"></i>';
                    } else if (cellValue === this.VALID_UNITS[1]) {
                        cell.innerHTML = '<i class="far fa-circle fa-4x"></i>';
                    } else {
                        if (game.units.human === this.VALID_UNITS[0]) {
                            cell.innerHTML = '<i class="fas fa-times fa-4x"></i>';
                        } else {
                            cell.innerHTML = '<i class="far fa-circle fa-4x"></i>';
                        }
                    }
                }
            }

            this.nodes.boardSection.classList.remove('d-none');

            if (game.winner) {
                this.nodes.result.innerHTML = 'It is a draw!';
                if (game.winner.result === game.units.human) {
                    this.nodes.result.innerHTML = 'You win!<i class="far fa-surprise fa-2x ml-4"></i>';
                } else if (game.winner.result === game.units.bot) {
                    this.nodes.result.innerHTML = 'Robot won!<i class="fas fa-robot fa-2x ml-4"></i>';
                }
                this.nodes.prompt.innerHTML = '<h5 class="m-0">Do you want to play again?<br>Choose your symbol</h5>';

                if (game.winner.coordinates) {
                    for (var c = 0; c < game.winner.coordinates.length; c++) {
                        var coordinate = game.winner.coordinates[c];
                        cell = this.nodes['cell' + coordinate[0] + '' + coordinate[1]];
                        cell.classList.add('winner-cell');
                    }
                }

                this.nodes.footerSection.classList.remove('d-none');
                this.nodes.startNewGame.classList.remove('d-none');
                // this.nodes.startNewGame.classList.add('bg-light');
                this.nodes.resultSection.classList.remove('d-none');

                setTimeout(function () {
                    this.nodes.footerSection.scrollIntoView({behavior: 'smooth', block: 'end', inline: 'nearest'})
                }.bind(this), 500);
            } else {
                this.nodes.footerSection.classList.add('d-none');
                this.nodes.startNewGame.classList.add('d-none');
                // this.nodes.startNewGame.classList.remove('bg-light');
                this.nodes.resultSection.classList.add('d-none');
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

        cellNodeClick: function (row, col) {
            var me = this;
            if (me.gameState.game.board[row][col]) {
                return;
            }
            me.gameState.game.board[row][col] = me.gameState.game.units.human;
            me.updateView(me.gameState);
            me.setHumanMove(row, col, function() {
                me.gameState.game.board[row][col] = '';
                me.updateView(me.gameState);
            })
        },

        startNewGameNodeClick: function (humanUnit) {
            if (this.gameState && this.gameState.game && this.gameState.game.winner) {
                this.deleteGame(this.startNewGame.bind(this, humanUnit));
            } else {
                this.startNewGame(humanUnit);
            }
        }
    };

    ttt.init();

})(window);
