{
  description = "Multi Architecture Nix Flake for PHP development";

  inputs = {
    nixpkgs.url = "github:nixos/nixpkgs?ref=nixos-unstable";
    flake-utils.url = "github:numtide/flake-utils";
  };

  outputs =
    {
      self,
      nixpkgs,
      flake-utils,
      ...
    }@inputs:

    flake-utils.lib.eachDefaultSystem (
      system:
      let
        pkgs = import nixpkgs {
          inherit system;
          config.allowUnfree = true;
        };

        mkScript =
          name: text:
          let
            script = pkgs.writeShellScriptBin name text;
          in
          script;

        scripts = [
          (mkScript "php-debug-adapter" ''
            node ${pkgs.vscode-extensions.xdebug.php-debug}/share/vscode/extensions/xdebug.php-debug/out/phpDebug.js
          '')
        ];

        phpWithExtensions = (
          pkgs.php85.buildEnv {
            extensions = (
              { enabled, all }:
              enabled
              ++ (with all; [
                xdebug
                intl
                mysqli
                bcmath
                curl
                zip
                soap
                mbstring
                gd
                redis
              ])
            );
            extraConfig = ''
              error_reporting = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED
              xdebug.mode=debug
              xdebug.start_with_request=yes
              xdebug.client_host=127.0.0.1
              xdebug.client_port=9003
              xdebug.log_level = 0
            '';
          }
        );

        devPackages = with nixpkgs; [
          # base stuff
          phpWithExtensions
          phpWithExtensions.packages.composer
          pkgs.nodejs_22
          pkgs.curl
          pkgs.zip
          pkgs.unzip
          # php packages
          pkgs.vscode-extensions.xdebug.php-debug
        ];

        shellHook = "";
      in
      {
        devShells = {
          default = pkgs.mkShell {
            name = "php-dev-shell";
            nativeBuildInputs = scripts;
            packages = devPackages;
            postShellHook = shellHook;
          };
        };
      }
    );
}
