#!/usr/bin/env python3
import subprocess
import sys


COMMANDS = {
    "generate-embeddings": ["php", "artisan", "visual-search:generate-embeddings"],
}


def main():
    if len(sys.argv) < 2 or sys.argv[1] not in COMMANDS:
        print("Usage: python manage.py generate-embeddings [--limit=N]", file=sys.stderr)
        return 2

    command = COMMANDS[sys.argv[1]] + sys.argv[2:]
    return subprocess.call(command)


if __name__ == "__main__":
    raise SystemExit(main())
