import { Resource } from 'globunet-angular/core/models/api/resource';
import {Client} from './client';

export class Trace extends Resource {
  title: string;
  script: string;
  status: number;
  output: string;
  error: string;
  executedAt: Date;
  finishedAt: Date;
  client: Client;
  commandType: string;
}
