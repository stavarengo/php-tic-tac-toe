#!/usr/bin/env bash

_EXITING=0
function _waitAll()
{
    for job in `jobs -p`
    do
        _info "Waitting $job..."
        wait $job
        _info "Job done: $job"
    done
}
function _sigint {
    _EXITING=1
}
function _exit {
    _info "Exiting..."

    if hash apachectl &>/dev/null; then
        _info "Asking Apache to gracefully stop..."
        apachectl -k graceful-stop
    fi

    _EXITING=1

    for job in `jobs -p`
    do
        echo "   Sending SIGTERM to job $job..."
        kill -SIGTERM $job 2> /dev/null
    done
}

trap _sigint SIGINT SIGTERM SIGQUIT
trap _exit EXIT

apache2-foreground -e debug &

_waitAll
