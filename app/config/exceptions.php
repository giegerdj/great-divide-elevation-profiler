<?php

namespace ESRGD;

class ESRGDException extends \Exception {};

class DatabaseException extends ESRGDException {}
class NotFoundException extends ESRGDException {}
