#include <unistd.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>

int main(int argc, char **argv) {
    setuid(0);

    char cmd[] = "/usr/local/bin/certbot";

    int size;
    for(int i = 0; i < argc; i++) {
        if (i == 0) {
            continue;
        }

        size += strlen(argv[i]) + 1;
    }

    char fullCmd[strlen(cmd) + size + 1];
    strcpy(fullCmd, cmd);

    for(int j = 0; j < argc; j++) {
        if (j == 0) {
            continue;
        }

        strcat(fullCmd, " ");
        strcat(fullCmd, argv[j]);
    }

    strcat(fullCmd, "\n");

    system(fullCmd);
}