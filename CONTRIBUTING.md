# Contributing to ATick for PHP

Thanks for your interest in ATick! Contributions of all kinds are welcome.

## Ways to contribute

- **Report a bug** — open an issue with a minimal reproduction (input PDF if you
  can share one, the exact `Atick::*` call and its options, the full error
  message, your OS and `php --version`).
- **Request a feature** — open an issue describing the use case.
- **Improve the docs** — fixes and clarifications to anything under `docs/` are
  very welcome; the docs are built with Sphinx (`pip install -r docs/requirements.txt`
  then `sphinx-build -b html docs docs/_build`).
- **Add an example** — a focused script under `examples/` that shows a real
  workflow helps everyone.

## Reporting bugs effectively

A good report includes:

1. What you ran (the `Atick::*` call and the options you passed).
2. What you expected to happen.
3. What actually happened (the full error or wrong output).
4. Environment: `Atick::version()`, `php --version`, operating system and CPU arch.

## Pull requests

For documentation and example changes:

1. Fork the repository and create a topic branch.
2. Keep changes focused and described clearly in the commit message.
3. Make sure the examples run (`php examples/sign_pfx.php`) and the docs build cleanly.
4. Open the pull request against `main`.

## Code of Conduct

By participating you agree to abide by our
[Code of Conduct](CODE_OF_CONDUCT.md).

## Security

Please do **not** open public issues for security problems — see
[SECURITY.md](SECURITY.md) for how to report them privately.
