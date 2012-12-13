#!/bin/bash
#emacs: -*- mode: shell-script; c-basic-offset: 4; tab-width: 4; indent-tabs-mode: t -*- 
#ex: set sts=4 ts=4 sw=4 noet:
#
# Sets up MATLABPATH to make Psychtoolbox-3 available in matlab.
# Just source this file before running Matlab, via
#
#  . /etc/ptb3/matlab.sh
#
# or start matlab by running ptb3-matlab
#
PTB3_MATLAB_ROOTDIR=/usr/share/matlab/site/m/psychtoolbox-3

if echo $MATLABPATH | grep -q psychtoolbox; then
	: echo "I: nothing to do -- we must have psychtoolbox in MATLABPATH already"
else
	if [ -d "$PTB3_MATLAB_ROOTDIR" ]; then
		export MATLABPATH=$(find "$PTB3_MATLAB_ROOTDIR/" -type d | grep -v private | tr '\n' ':')$MATLABPATH
	else
		echo "I: nothing to do -- there is no $PTB3_ROOTDIR"
	fi
fi
