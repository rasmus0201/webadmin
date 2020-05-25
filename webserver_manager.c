#include <unistd.h>
#include <stdlib.h>
#include <stdio.h>
#include <string.h>
#include <stdbool.h>
#include <pwd.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <errno.h>

#define EXIT_SUCCESS 0
#define EXIT_FAILURE 1

struct passwd* getUser() {
    register struct passwd *pw;
    register uid_t uid;
    int c;

    uid = geteuid();
    pw = getpwuid(uid);

    if (pw) {
        return pw;
    }

    fprintf(stderr, "Cannot find username for UID %u\n", (unsigned) uid);
    exit (EXIT_FAILURE);
}

bool file_exists(char *filename) {
    struct stat buffer;
    return (stat(filename, &buffer) == 0);
}

int main(int argc, char **argv) {
    setuid(0);

    if (argc < 3) {
        return EXIT_FAILURE;
    }

    char *action = strtok(argv[1], " ");
    char *vHostFileName = strtok(argv[2], " ");
    char *webserverConfigPath = "/etc/nginx/";
    char availablePath[] = "sites-available/";
    char enabledPath[] = "sites-enabled/";

    // Don't allow relative paths (should also be able to hanlde ../ paths)
    if (strstr(vHostFileName, "./") != NULL) {
        return EXIT_FAILURE;
    }

    // Permissions
    mode_t permissions = 0744; // Only owner can edit
    register struct passwd *user = getUser(); // Should be root user

    if (user->pw_uid != 0) {
        return EXIT_FAILURE;
    }

    // Build path to sites-available
    char absoluteAvailablePath[strlen(webserverConfigPath) + strlen(availablePath) + strlen(vHostFileName)];
    strcpy(absoluteAvailablePath, webserverConfigPath);
    strcat(absoluteAvailablePath, availablePath);
    strcat(absoluteAvailablePath, vHostFileName);

    // Build path to sites-enabled
    char absoluteEnabledPath[strlen(absoluteAvailablePath)];
    strcpy(absoluteEnabledPath, webserverConfigPath);
    strcat(absoluteEnabledPath, enabledPath);
    strcat(absoluteEnabledPath, vHostFileName);

    if (strcmp(action, "enable") == 0) {
        if (argc != 4) {
            printf("%s\n", "When using the enable action, you should specify 3rd parameter tmpLocation for a file to copy from.");
            return EXIT_FAILURE;
        }

        printf("Attempting to store config at '%s'\n", absoluteAvailablePath);

        char *tmpLocation = strtok(argv[3], " ");

        rename(tmpLocation, absoluteAvailablePath);
        if (!file_exists(absoluteAvailablePath)) {
            printf("%s\n", strerror(errno));
            return EXIT_FAILURE;
        }

        chown(absoluteAvailablePath, user->pw_uid, user->pw_gid);
        chmod(absoluteAvailablePath, permissions);
    } else if (strcmp(action, "disable") == 0) {
        printf("Removing link '%s'\n", absoluteEnabledPath);

        unlink(absoluteEnabledPath);
    } else if (strcmp(action, "link") == 0) {
        printf("Creating link '%s' -> '%s'\n", absoluteAvailablePath, absoluteEnabledPath);

        symlink(absoluteAvailablePath, absoluteEnabledPath);
    } else if (strcmp(action, "test") == 0) {
        system("nginx -t");
    } else {
        return EXIT_FAILURE;
    }

    return EXIT_SUCCESS;
}

