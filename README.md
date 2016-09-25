# Totec Server

## Installation

### Phalcon
```bash
$ brew tap homebrew/homebrew-php
```

```bash
$ brew install php70-phalcon
```

```bash
$ php composer.phar install
```

### Command Line Tool

```bash
$ git clone git://github.com/phalcon/phalcon-devtools.git ~/phalcon-devtools
```

```bash
$ sh ~/phalcon-devtools/phalcon.sh
```

```bash
$ ln -s ~/phalcon-devtools/phalcon.php ~/phalcon-devtools/phalcon
```

```bash
$ chmod +x ~/phalcon-devtools/phalcon
```

```bash
$ source ~/.bash_profile
```

```bash
$ phalcon commands
```

## Run php-code-fixer and check syntax error before commit

```bash
$ brew install homebrew/php/php-cs-fixer
```

```bash
$ cp pre-commit .git/hooks/pre-commit
```

```bash
$ chmod 755 .git/hooks/pre-commit
```

## Integrating with PhpStorm IDE

1. Click External Libraries
2. Select php7
3. Click + button
4. Select vendor/phalcon/devtools/ide
5. Click OK and Apply
