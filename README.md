# ABCars Frontend

This project was generated with Angular CLI, based on the [Angular](https://angular.io/) framework, 
its use includes Angular Material, libraries, components, 
and any other visual, auditory, functional or other element to 
carry out the total development of ABCars .

This project was generated with :

Angular CLI: 15.0.4
Node: 18.12.1
Package Manager: npm 8.19.2

Angular: 15.0.4
... animations, cli, common, compiler, compiler-cli, core, forms
... platform-browser, platform-browser-dynamic, router

Package                         Version
---------------------------------------------------------
@angular-devkit/architect       0.1500.4
@angular-devkit/build-angular   15.0.4
@angular-devkit/core            15.0.4
@angular-devkit/schematics      15.0.4
@angular/cdk                    15.0.3
@angular/flex-layout            12.0.0-beta.34
@angular/material               15.0.3
@schematics/angular             15.

## NVM install
NVM stands for Node Version Manager. Is an excellent tool to install specific version or 
manage multiple versions of Node. https//github.com/nvm-sh/nvm

If you have several projects with different versions of Node, then install NVM in your machine to easy change
to version 18.12.1 of node each time you edit this proyect. This will also change the npm to a compatible version.

.nvmrc file allows to set the specific version of Node for each project.

Type the next command to read node version from .nvmrc file and set it into your project:

nvm use

or type the next command to manually set the specific version:

nvm use v18.12.1

## Development Install

Run `npm install` for install dependencies of node_modules

## Create file of environments

You need to create the environment.prod.ts file to stablish your environment variables:

Create that file and put this content inside:

export const environment = {
  production: true,
  baseUrl: 'URL of your local backend environment'
};

## Development server

Run `ng serve` for a dev server. Navigate to `http://localhost:4200/`. The app will automatically reload if you change any of the source files.

The AB Cars is Private Software developed by Organization Strega System
Copyright (c) 2021 Strega System - ABCars.mx