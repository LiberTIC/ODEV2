#!/bin/sh

curator delete indices --older-than 2 --time-unit days --timestring '%Y.%m.%d' --prefix .marvel-
