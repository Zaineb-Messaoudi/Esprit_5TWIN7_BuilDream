# GitHub repository controls

These controls are repository settings, not application files. Apply them in
GitHub once after the workflow checks have completed successfully.

## `main` branch protection

Create a branch protection rule for `main` with:

- require a pull request before merging;
- require at least one approval;
- dismiss stale approvals after a new commit;
- require conversation resolution;
- require status checks before merging;
- require the branch to be up to date;
- require these checks:
  - `Build & Test / Frontend Build & Lint`;
  - `Build & Test / PHP Tests (PHP 8.3)`;
  - `Build & Test / Code Style (Pint)`;
  - `Build & Test / Security Audit`;
  - the two `CodeQL / Analyze ...` matrix checks;
- block force pushes and branch deletion;
- do not allow bypassing these rules for normal contributors.

Do not require the deployment job on pull requests: it runs only after a merge
to `main`.

## GitHub Environments

Create a `production` environment containing only:

- `DOCKERHUB_USERNAME`;
- `DOCKERHUB_TOKEN`.

Store application secrets in the deployment host's private `.env`, not in the
repository. Disable required reviewers only if the validation requires a fully
automatic merge-to-deployment flow.
